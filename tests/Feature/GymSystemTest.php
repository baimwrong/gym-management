<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassBooking;
use App\Models\FitnessClass;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\MemberSubscription;
use App\Models\Product;
use App\Models\User;
use App\Services\BillingService;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GymSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@testgym.local',
            'password' => 'secret123',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_login_rate_limiting_triggers_after_max_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login.post'), [
                'email' => 'wrong@testgym.local',
                'password' => 'invalidpassword',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt should be throttled
        $response = $this->post(route('login.post'), [
            'email' => 'wrong@testgym.local',
            'password' => 'invalidpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
    }

    public function test_kiosk_member_checkin_and_checkout(): void
    {
        $plan = MembershipPlan::create([
            'name' => 'Gold Pass',
            'slug' => 'gold-pass',
            'duration_type' => 'months',
            'duration_count' => 1,
            'price' => 50,
            'signup_fee' => 0,
            'includes_classes' => true,
            'is_active' => true,
        ]);

        $member = Member::create([
            'member_code' => 'GYM-2026-9001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'email' => 'john.doe@test.local',
            'barcode_value' => 'GYM-2026-9001',
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);

        MemberSubscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price_paid' => 50,
            'status' => 'active',
        ]);

        // First scan: Check-in via toggle
        $response = $this->actingAs($this->adminUser)
            ->post(route('attendance.process'), [
                'code' => 'GYM-2026-9001',
                'action' => 'toggle',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'member_id' => $member->id,
            'check_out_time' => null,
        ]);

        // Second scan: Check-out via toggle
        $response2 = $this->actingAs($this->adminUser)
            ->post(route('attendance.process'), [
                'code' => 'GYM-2026-9001',
                'action' => 'toggle',
            ]);

        $response2->assertSessionHas('success');

        $this->assertDatabaseMissing('attendances', [
            'member_id' => $member->id,
            'check_out_time' => null,
        ]);
    }

    public function test_kiosk_rejects_expired_and_frozen_members(): void
    {
        $expiredMember = Member::create([
            'member_code' => 'GYM-2026-9002',
            'first_name' => 'Jane',
            'last_name' => 'Expired',
            'gender' => 'female',
            'barcode_value' => 'GYM-2026-9002',
            'status' => 'expired',
            'joined_date' => now()->subMonths(6)->toDateString(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('attendance.process'), [
                'code' => 'GYM-2026-9002',
                'action' => 'check_in',
            ]);

        $response->assertSessionHas('error');

        $frozenMember = Member::create([
            'member_code' => 'GYM-2026-9003',
            'first_name' => 'Bob',
            'last_name' => 'Frozen',
            'gender' => 'male',
            'barcode_value' => 'GYM-2026-9003',
            'status' => 'frozen',
            'joined_date' => now()->subMonths(2)->toDateString(),
        ]);

        $response2 = $this->actingAs($this->adminUser)
            ->post(route('attendance.process'), [
                'code' => 'GYM-2026-9003',
                'action' => 'check_in',
            ]);

        $response2->assertSessionHas('error');
    }

    public function test_class_booking_requires_class_eligible_membership_plan(): void
    {
        $basicPlan = MembershipPlan::create([
            'name' => 'Basic Gym Only',
            'slug' => 'basic-gym',
            'duration_type' => 'months',
            'duration_count' => 1,
            'price' => 30,
            'signup_fee' => 0,
            'includes_classes' => false,
            'is_active' => true,
        ]);

        $member = Member::create([
            'member_code' => 'GYM-2026-9004',
            'first_name' => 'Alice',
            'last_name' => 'Basic',
            'gender' => 'female',
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);

        MemberSubscription::create([
            'member_id' => $member->id,
            'plan_id' => $basicPlan->id,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price_paid' => 30,
            'status' => 'active',
        ]);

        $todayDay = Carbon::today()->format('l');

        $fitnessClass = FitnessClass::create([
            'title' => 'HIIT Blast',
            'room' => 'Studio 1',
            'capacity' => 15,
            'day_of_week' => $todayDay,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'difficulty_level' => 'all_levels',
            'color_tag' => '#3b82f6',
            'is_active' => true,
        ]);

        // Attempt to book without plan eligibility
        $response = $this->actingAs($this->adminUser)
            ->post(route('classes.book', $fitnessClass), [
                'member_id' => $member->id,
                'booking_date' => Carbon::today()->toDateString(),
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('class_bookings', [
            'class_id' => $fitnessClass->id,
            'member_id' => $member->id,
        ]);

        // Upgrade plan to include classes
        $basicPlan->update(['includes_classes' => true]);

        $response2 = $this->actingAs($this->adminUser)
            ->post(route('classes.book', $fitnessClass), [
                'member_id' => $member->id,
                'booking_date' => Carbon::today()->toDateString(),
            ]);

        $response2->assertSessionHas('success');
        $this->assertDatabaseHas('class_bookings', [
            'class_id' => $fitnessClass->id,
            'member_id' => $member->id,
        ]);
    }

    public function test_class_booking_prevents_overbooking_capacity(): void
    {
        $plan = MembershipPlan::create([
            'name' => 'Premium VIP',
            'slug' => 'premium-vip',
            'duration_type' => 'months',
            'duration_count' => 1,
            'price' => 100,
            'signup_fee' => 0,
            'includes_classes' => true,
            'is_active' => true,
        ]);

        $todayDay = Carbon::today()->format('l');

        $fitnessClass = FitnessClass::create([
            'title' => 'Spin Cycle',
            'room' => 'Studio 2',
            'capacity' => 1, // Only 1 spot available
            'day_of_week' => $todayDay,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'difficulty_level' => 'intermediate',
            'color_tag' => '#ef4444',
            'is_active' => true,
        ]);

        $member1 = Member::create([
            'member_code' => 'GYM-2026-9005',
            'first_name' => 'User',
            'last_name' => 'One',
            'gender' => 'male',
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);
        MemberSubscription::create([
            'member_id' => $member1->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price_paid' => 100,
            'status' => 'active',
        ]);

        $member2 = Member::create([
            'member_code' => 'GYM-2026-9006',
            'first_name' => 'User',
            'last_name' => 'Two',
            'gender' => 'female',
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);
        MemberSubscription::create([
            'member_id' => $member2->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price_paid' => 100,
            'status' => 'active',
        ]);

        // First booking succeeds
        $res1 = $this->actingAs($this->adminUser)
            ->post(route('classes.book', $fitnessClass), [
                'member_id' => $member1->id,
                'booking_date' => Carbon::today()->toDateString(),
            ]);
        $res1->assertSessionHas('success');

        // Second booking exceeds capacity and is rejected
        $res2 = $this->actingAs($this->adminUser)
            ->post(route('classes.book', $fitnessClass), [
                'member_id' => $member2->id,
                'booking_date' => Carbon::today()->toDateString(),
            ]);
        $res2->assertSessionHas('error');

        $this->assertEquals(1, ClassBooking::where('class_id', $fitnessClass->id)->count());
    }

    public function test_pos_checkout_deducts_inventory_and_generates_invoice(): void
    {
        $product = Product::create([
            'sku' => 'PROT-WHEY-01',
            'name' => 'Whey Protein Isolate 1kg',
            'category' => 'supplements',
            'cost_price' => 25.00,
            'selling_price' => 45.00,
            'stock_quantity' => 10,
            'reorder_level' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('pos.checkout'), [
                'payment_method' => 'cash',
                'discount_amount' => 5.00,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $this->assertEquals(8, $product->fresh()->stock_quantity);

        $invoice = Invoice::latest()->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals('pos_retail', $invoice->invoice_type);
        $this->assertCount(1, $invoice->items);
    }

    public function test_pos_checkout_rejects_insufficient_stock(): void
    {
        $product = Product::create([
            'sku' => 'ENERG-BAR-01',
            'name' => 'Energy Bar',
            'category' => 'snacks',
            'cost_price' => 1.00,
            'selling_price' => 2.50,
            'stock_quantity' => 1,
            'reorder_level' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('pos.checkout'), [
                'payment_method' => 'cash',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5, // More than 1
                    ],
                ],
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, $product->fresh()->stock_quantity);
    }

    public function test_member_code_generation_is_unique_and_sequential(): void
    {
        $code1 = Member::generateMemberCode();
        Member::create([
            'member_code' => $code1,
            'first_name' => 'First',
            'last_name' => 'Tester',
            'gender' => 'male',
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);

        $code2 = Member::generateMemberCode();
        $this->assertNotEquals($code1, $code2);

        $year = date('Y');
        $this->assertStringStartsWith("GYM-{$year}-", $code1);
        $this->assertStringStartsWith("GYM-{$year}-", $code2);
    }

    public function test_invoice_number_generation_is_unique_and_sequential(): void
    {
        $inv1 = BillingService::generateInvoiceNumber();
        Invoice::create([
            'invoice_number' => $inv1,
            'customer_name' => 'Guest One',
            'subtotal' => 50,
            'tax_amount' => 4,
            'discount_amount' => 0,
            'total_amount' => 54,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'invoice_type' => 'general',
        ]);

        $inv2 = BillingService::generateInvoiceNumber();
        $this->assertNotEquals($inv1, $inv2);

        $year = date('Y');
        $this->assertStringStartsWith("INV-{$year}-", $inv1);
        $this->assertStringStartsWith("INV-{$year}-", $inv2);
    }

    public function test_qrcode_and_barcode_generation(): void
    {
        $qr = QrCodeService::generateSvg('GYM-2026-1001', 180);
        $this->assertStringContainsString('<svg', $qr);
        $this->assertStringContainsString('</svg>', $qr);

        $barcode = QrCodeService::generateBarcodeSvg('GYM-2026-1001', 240, 60);
        $this->assertStringContainsString('<svg', $barcode);
        $this->assertStringContainsString('GYM-2026-1001', $barcode);
        $this->assertStringContainsString('</svg>', $barcode);
    }

    public function test_midnight_turnstile_checkin_and_checkout_across_days(): void
    {
        $plan = MembershipPlan::create([
            'name' => 'Unlimited Pass',
            'slug' => 'unlimited-pass',
            'duration_type' => 'months',
            'duration_count' => 1,
            'price' => 60,
            'signup_fee' => 0,
            'includes_classes' => true,
            'is_active' => true,
        ]);

        $member = Member::create([
            'member_code' => 'GYM-2026-9050',
            'first_name' => 'Night',
            'last_name' => 'Owl',
            'gender' => 'male',
            'barcode_value' => 'GYM-2026-9050',
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);

        MemberSubscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price_paid' => 60,
            'status' => 'active',
        ]);

        // Checked in at 11:30 PM yesterday
        $checkInTime = Carbon::now()->yesterday()->setTime(23, 30, 0);
        $attendance = Attendance::create([
            'member_id' => $member->id,
            'check_in_time' => $checkInTime,
            'check_in_method' => 'kiosk',
            'check_out_time' => null,
        ]);

        // Scanning out at 00:30 AM today (1 hour later across midnight boundary)
        Carbon::setTestNow(Carbon::now()->today()->setTime(0, 30, 0));

        $response = $this->actingAs($this->adminUser)
            ->post(route('attendance.process'), [
                'code' => 'GYM-2026-9050',
                'action' => 'toggle',
            ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Checked out successfully', session('success'));

        $attendance->refresh();
        $this->assertNotNull($attendance->check_out_time);
        $this->assertEquals(60, $attendance->duration_minutes);

        // Verify no second duplicate checkin record was created
        $this->assertEquals(1, Attendance::where('member_id', $member->id)->count());

        Carbon::setTestNow(); // Reset test time
    }

    public function test_future_start_date_subscription_rejected_at_turnstile(): void
    {
        $plan = MembershipPlan::create([
            'name' => 'Future Plan',
            'slug' => 'future-plan',
            'duration_type' => 'months',
            'duration_count' => 1,
            'price' => 50,
            'signup_fee' => 0,
            'is_active' => true,
        ]);

        $member = Member::create([
            'member_code' => 'GYM-2026-9060',
            'first_name' => 'Early',
            'last_name' => 'Bird',
            'gender' => 'male',
            'barcode_value' => 'GYM-2026-9060',
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);

        // Subscription starts 5 days from now
        MemberSubscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(35)->toDateString(),
            'price_paid' => 50,
            'status' => 'active',
        ]);

        // Attempt scan today
        $response = $this->actingAs($this->adminUser)
            ->post(route('attendance.process'), [
                'code' => 'GYM-2026-9060',
                'action' => 'check_in',
            ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('starts on', session('error'));
        $this->assertDatabaseMissing('attendances', ['member_id' => $member->id]);
    }

    public function test_locker_collision_and_billing_invoice(): void
    {
        $locker1 = \App\Models\Locker::create([
            'locker_number' => 'L-101',
            'zone' => 'Main Wing',
            'status' => 'available',
            'rental_fee_monthly' => 20.00,
        ]);

        $locker2 = \App\Models\Locker::create([
            'locker_number' => 'L-102',
            'zone' => 'Main Wing',
            'status' => 'available',
            'rental_fee_monthly' => 20.00,
        ]);

        $member1 = Member::create([
            'member_code' => 'GYM-2026-9070',
            'first_name' => 'Sam',
            'last_name' => 'Locker',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $member2 = Member::create([
            'member_code' => 'GYM-2026-9071',
            'first_name' => 'Kelly',
            'last_name' => 'Locker',
            'gender' => 'female',
            'status' => 'active',
        ]);

        // Assign locker 1 to member 1 for 3 months
        $response = $this->actingAs($this->adminUser)
            ->post(route('lockers.assign', $locker1), [
                'member_id' => $member1->id,
                'duration_months' => 3,
                'payment_method' => 'cash',
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals('occupied', $locker1->fresh()->status);
        $this->assertEquals($member1->id, $locker1->fresh()->assigned_member_id);

        // Verify billing invoice was generated ($20 x 3 = $60)
        $invoice = Invoice::where('invoice_type', 'locker_rental')->where('member_id', $member1->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(60.00, $invoice->total_amount);

        // Attempt to assign locker 1 again to member 2 -> should be rejected (collision)
        $resCollision = $this->actingAs($this->adminUser)
            ->post(route('lockers.assign', $locker1), [
                'member_id' => $member2->id,
                'duration_months' => 1,
            ]);
        $resCollision->assertSessionHas('error');

        // Attempt to assign locker 2 to member 1 (who already has locker 1) -> should be rejected
        $resDuplicate = $this->actingAs($this->adminUser)
            ->post(route('lockers.assign', $locker2), [
                'member_id' => $member1->id,
                'duration_months' => 1,
            ]);
        $resDuplicate->assertSessionHas('error');
    }

    public function test_trainer_assignment_deducts_plan_pt_quota(): void
    {
        $trainerUser = User::create([
            'name' => 'Coach Dan',
            'email' => 'coach.dan@testgym.local',
            'password' => 'secret123',
            'role' => 'trainer',
            'status' => 'active',
        ]);

        $trainer = \App\Models\Trainer::create([
            'user_id' => $trainerUser->id,
            'full_name' => 'Coach Dan',
            'specialties' => ['strength'],
            'hourly_rate' => 60,
            'is_available' => true,
        ]);

        $plan = MembershipPlan::create([
            'name' => 'VIP Coach Pass',
            'slug' => 'vip-coach-pass',
            'duration_type' => 'months',
            'duration_count' => 1,
            'price' => 150,
            'includes_pt_sessions' => 5,
            'is_active' => true,
        ]);

        $member = Member::create([
            'member_code' => 'GYM-2026-9080',
            'first_name' => 'Leo',
            'last_name' => 'Trainee',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $sub = MemberSubscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price_paid' => 150,
            'remaining_pt_sessions' => 5,
            'status' => 'active',
        ]);

        // Assign 2 sessions from plan quota
        $response = $this->actingAs($this->adminUser)
            ->post(route('trainers.assign', $trainer), [
                'member_id' => $member->id,
                'package_name' => 'VIP Orientation Pack',
                'total_sessions' => 2,
                'start_date' => now()->toDateString(),
                'use_plan_sessions' => 1,
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals(3, $sub->fresh()->remaining_pt_sessions);
    }

    public function test_rbac_restrictions_for_trainer_role(): void
    {
        $trainerUser = User::create([
            'name' => 'Coach Sara',
            'email' => 'coach.sara@testgym.local',
            'password' => 'secret123',
            'role' => 'trainer',
            'status' => 'active',
        ]);

        // Trainer cannot access Settings
        $resSettings = $this->actingAs($trainerUser)->get(route('settings.index'));
        $resSettings->assertStatus(403);

        // Trainer cannot access Financial Reports
        $resReports = $this->actingAs($trainerUser)->get(route('reports.index'));
        $resReports->assertStatus(403);

        // Trainer cannot access Bulk Import Center
        $resImport = $this->actingAs($trainerUser)->get(route('import.index'));
        $resImport->assertStatus(403);

        // Trainer cannot access POS Register
        $resPos = $this->actingAs($trainerUser)->get(route('pos.index'));
        $resPos->assertStatus(403);
    }

    public function test_ajax_kiosk_json_response(): void
    {
        $plan = MembershipPlan::create([
            'name' => 'Digital Pass',
            'slug' => 'digital-pass',
            'duration_type' => 'months',
            'duration_count' => 1,
            'price' => 40,
            'is_active' => true,
        ]);

        $member = Member::create([
            'member_code' => 'GYM-2026-9090',
            'first_name' => 'Digital',
            'last_name' => 'Scanner',
            'gender' => 'male',
            'barcode_value' => 'GYM-2026-9090',
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);

        MemberSubscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price_paid' => 40,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('attendance.process'), [
                'code' => 'GYM-2026-9090',
                'action' => 'toggle',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'success',
            'member' => [
                'code' => 'GYM-2026-9090',
                'name' => 'Digital Scanner',
            ],
        ]);
        $response->assertJsonStructure(['currently_inside', 'attendance']);
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('profile.password'), [
                'current_password' => 'secret123',
                'new_password' => 'brandnewpassword123',
                'new_password_confirmation' => 'brandnewpassword123',
            ]);

        $response->assertSessionHas('success');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('brandnewpassword123', $this->adminUser->fresh()->password));
    }
}
