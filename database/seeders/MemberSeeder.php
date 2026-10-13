<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Member;
use App\Models\MemberHealthMetric;
use App\Models\MembershipPlan;
use App\Models\MemberSubscription;
use App\Models\Trainer;
use App\Models\TrainerMemberAssignment;
use App\Models\WorkoutPlan;
use App\Models\DietPlan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $silverPlan = MembershipPlan::where('slug', 'silver-monthly')->first();
        $goldPlan = MembershipPlan::where('slug', 'gold-quarterly')->first();
        $platinumPlan = MembershipPlan::where('slug', 'platinum-vip-annual')->first();
        $crossfitPlan = MembershipPlan::where('slug', 'crossfit-boxing-special')->first();

        $trainerAlex = Trainer::first();
        $trainerMaya = Trainer::skip(1)->first();

        $membersData = [
            [
                'member_code' => 'GYM-2026-1001',
                'first_name' => 'James',
                'last_name' => 'Holloway',
                'gender' => 'male',
                'date_of_birth' => '1993-05-14',
                'email' => 'james.holloway@example.com',
                'phone' => '+1 (555) 234-8901',
                'emergency_contact_name' => 'Rachel Holloway (Wife)',
                'emergency_contact_phone' => '+1 (555) 234-8909',
                'blood_type' => 'O+',
                'address' => '142 Pine Street, Apt 4B',
                'city' => 'Metro City',
                'postal_code' => '10002',
                'fitness_goals' => ['Hypertrophy', 'Increase Bench Press', 'General Fitness'],
                'medical_notes' => 'Minor past right shoulder impingement; avoid behind-the-neck presses.',
                'status' => 'active',
                'plan' => $platinumPlan,
                'weight' => 84.5,
                'height' => 182.0,
                'trainer' => $trainerAlex,
            ],
            [
                'member_code' => 'GYM-2026-1002',
                'first_name' => 'Sophia',
                'last_name' => 'Chen',
                'gender' => 'female',
                'date_of_birth' => '1997-11-20',
                'email' => 'sophia.chen@example.com',
                'phone' => '+1 (555) 345-6712',
                'emergency_contact_name' => 'David Chen (Father)',
                'emergency_contact_phone' => '+1 (555) 345-6799',
                'blood_type' => 'A+',
                'address' => '88 Lexington Avenue',
                'city' => 'Metro City',
                'postal_code' => '10003',
                'fitness_goals' => ['Fat Loss', 'Marathon Prep', 'Core Strength'],
                'medical_notes' => 'No reported injuries or medical restrictions.',
                'status' => 'active',
                'plan' => $goldPlan,
                'weight' => 58.2,
                'height' => 165.0,
                'trainer' => $trainerMaya,
            ],
            [
                'member_code' => 'GYM-2026-1003',
                'first_name' => 'Derrick',
                'last_name' => 'Miller',
                'gender' => 'male',
                'date_of_birth' => '1988-03-08',
                'email' => 'derrick.miller@example.com',
                'phone' => '+1 (555) 456-7823',
                'emergency_contact_name' => 'Lisa Miller',
                'emergency_contact_phone' => '+1 (555) 456-7888',
                'blood_type' => 'B+',
                'address' => '305 Oakwood Boulevard',
                'city' => 'Metro City',
                'postal_code' => '10004',
                'fitness_goals' => ['CrossFit Competitions', 'Olympic Lifts'],
                'medical_notes' => 'None',
                'status' => 'active',
                'plan' => $crossfitPlan,
                'weight' => 91.0,
                'height' => 185.0,
                'trainer' => $trainerAlex,
            ],
            [
                'member_code' => 'GYM-2026-1004',
                'first_name' => 'Emily',
                'last_name' => 'Watson',
                'gender' => 'female',
                'date_of_birth' => '2001-08-25',
                'email' => 'emily.watson@example.com',
                'phone' => '+1 (555) 567-8934',
                'emergency_contact_name' => 'Grace Watson',
                'emergency_contact_phone' => '+1 (555) 567-8999',
                'blood_type' => 'AB+',
                'address' => '19 University Court',
                'city' => 'Metro City',
                'postal_code' => '10005',
                'fitness_goals' => ['Flexibility', 'Posture Improvement', 'Stress Relief'],
                'medical_notes' => 'Asthma inhaler carried in gym bag.',
                'status' => 'active',
                'plan' => $goldPlan,
                'weight' => 54.0,
                'height' => 162.0,
                'trainer' => $trainerMaya,
            ],
            [
                'member_code' => 'GYM-2026-1005',
                'first_name' => 'Lucas',
                'last_name' => 'Granger',
                'gender' => 'male',
                'date_of_birth' => '1995-12-02',
                'email' => 'lucas.granger@example.com',
                'phone' => '+1 (555) 678-9045',
                'emergency_contact_name' => 'Tom Granger',
                'emergency_contact_phone' => '+1 (555) 678-9000',
                'blood_type' => 'O-',
                'address' => '712 Industrial Way',
                'city' => 'Metro City',
                'postal_code' => '10006',
                'fitness_goals' => ['Strength & Power', 'Rebuild Knee after ACL'],
                'medical_notes' => 'ACL reconstructed 2024. Clear to lift with knee sleeves.',
                'status' => 'active',
                'plan' => $silverPlan,
                'weight' => 79.0,
                'height' => 176.0,
                'trainer' => null,
            ],
            [
                'member_code' => 'GYM-2026-1006',
                'first_name' => 'Chloe',
                'last_name' => 'Bennett',
                'gender' => 'female',
                'date_of_birth' => '1992-04-18',
                'email' => 'chloe.bennett@example.com',
                'phone' => '+1 (555) 789-0156',
                'emergency_contact_name' => 'Sam Bennett',
                'emergency_contact_phone' => '+1 (555) 789-0199',
                'blood_type' => 'A-',
                'address' => '42 Harbor View Terrace',
                'city' => 'Metro City',
                'postal_code' => '10007',
                'fitness_goals' => ['Muscle Tone', 'Cardiovascular Endurance'],
                'medical_notes' => 'None',
                'status' => 'expired',
                'plan' => $silverPlan,
                'weight' => 62.0,
                'height' => 168.0,
                'trainer' => null,
            ]
        ];

        foreach ($membersData as $data) {
            $member = Member::updateOrCreate(
                ['member_code' => $data['member_code']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'gender' => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'emergency_contact_name' => $data['emergency_contact_name'],
                    'emergency_contact_phone' => $data['emergency_contact_phone'],
                    'blood_type' => $data['blood_type'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'postal_code' => $data['postal_code'],
                    'fitness_goals' => $data['fitness_goals'],
                    'medical_notes' => $data['medical_notes'],
                    'barcode_value' => $data['member_code'],
                    'rfid_card_number' => 'RFID-' . substr(md5($data['member_code']), 0, 8),
                    'status' => $data['status'],
                    'joined_date' => Carbon::now()->subMonths(3)->toDateString(),
                ]
            );

            // Log baseline and progress health metrics
            MemberHealthMetric::create([
                'member_id' => $member->id,
                'recorded_date' => Carbon::now()->subMonths(2)->toDateString(),
                'weight_kg' => $data['weight'] + 2.5,
                'height_cm' => $data['height'],
                'body_fat_percentage' => 19.5,
                'muscle_mass_kg' => 38.0,
                'chest_cm' => 102.0,
                'waist_cm' => 84.0,
                'arms_cm' => 37.0,
                'thighs_cm' => 59.0,
                'notes' => 'Baseline measurement at enrollment.',
            ]);

            MemberHealthMetric::create([
                'member_id' => $member->id,
                'recorded_date' => Carbon::now()->subDays(5)->toDateString(),
                'weight_kg' => $data['weight'],
                'height_cm' => $data['height'],
                'body_fat_percentage' => 17.8,
                'muscle_mass_kg' => 39.5,
                'chest_cm' => 104.0,
                'waist_cm' => 81.5,
                'arms_cm' => 38.2,
                'thighs_cm' => 60.0,
                'notes' => 'Follow-up measurement: solid body composition progress.',
            ]);

            // Create subscription
            $plan = $data['plan'];
            if ($plan) {
                $isExpired = ($data['status'] === 'expired');
                $startDate = $isExpired ? Carbon::now()->subMonths(2) : Carbon::now()->subDays(15);
                $endDate = $isExpired ? Carbon::now()->subDays(3) : Carbon::now()->addMonths($plan->duration_count);

                MemberSubscription::create([
                    'member_id' => $member->id,
                    'plan_id' => $plan->id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'status' => $data['status'],
                    'price_paid' => $plan->price,
                    'remaining_pt_sessions' => $plan->includes_pt_sessions,
                    'is_auto_renew' => true,
                    'notes' => "Active package: {$plan->name}",
                ]);

                // Generate invoice
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-2026-' . sprintf('%05d', $member->id),
                    'member_id' => $member->id,
                    'customer_name' => $member->full_name,
                    'subtotal' => $plan->price,
                    'tax_amount' => round($plan->price * 0.085, 2),
                    'discount_amount' => 0.00,
                    'total_amount' => round($plan->price * 1.085, 2),
                    'payment_status' => 'paid',
                    'payment_method' => 'credit_card',
                    'invoice_type' => 'membership',
                    'paid_at' => $startDate,
                    'due_date' => $startDate,
                    'notes' => "Invoice for {$plan->name}",
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_description' => "Membership Plan: {$plan->name}",
                    'unit_price' => $plan->price,
                    'quantity' => 1,
                    'total_price' => $plan->price,
                ]);
            }

            // Assign trainer if applicable
            if ($data['trainer']) {
                TrainerMemberAssignment::create([
                    'trainer_id' => $data['trainer']->id,
                    'member_id' => $member->id,
                    'package_name' => '1-on-1 Performance Coaching',
                    'total_sessions' => 12,
                    'completed_sessions' => 4,
                    'status' => 'active',
                    'start_date' => Carbon::now()->subMonths(1)->toDateString(),
                    'notes' => 'Focusing on strength progression and technique.',
                ]);
            }
        }
    }
}
