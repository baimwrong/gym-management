-- ====================================================================
-- GYM MANAGEMENT SYSTEM - POSTGRESQL 18 INITIALIZATION DDL
-- Compatible with PostgreSQL 18 & Laravel 13
-- ====================================================================

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP WITH TIME ZONE NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) DEFAULT 'member' NOT NULL,
    phone VARCHAR(30) NULL,
    avatar VARCHAR(255) NULL,
    status VARCHAR(20) DEFAULT 'active' NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);

-- 2. Members Table
CREATE TABLE IF NOT EXISTS members (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    member_code VARCHAR(30) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender VARCHAR(20) DEFAULT 'male' NOT NULL,
    date_of_birth DATE NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    emergency_contact_name VARCHAR(100) NULL,
    emergency_contact_phone VARCHAR(30) NULL,
    blood_type VARCHAR(10) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    medical_notes TEXT NULL,
    fitness_goals JSONB NULL,
    barcode_value VARCHAR(100) NULL,
    rfid_card_number VARCHAR(100) NULL,
    avatar VARCHAR(255) NULL,
    status VARCHAR(30) DEFAULT 'active' NOT NULL,
    joined_date DATE DEFAULT CURRENT_DATE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_members_code ON members(member_code);
CREATE INDEX IF NOT EXISTS idx_members_status_created ON members(status, created_at);

-- 3. Member Health Metrics
CREATE TABLE IF NOT EXISTS member_health_metrics (
    id BIGSERIAL PRIMARY KEY,
    member_id BIGINT NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    recorded_date DATE NOT NULL,
    weight_kg NUMERIC(5,2) NULL,
    height_cm NUMERIC(5,2) NULL,
    bmi NUMERIC(4,1) NULL,
    body_fat_percentage NUMERIC(4,1) NULL,
    muscle_mass_kg NUMERIC(4,1) NULL,
    chest_cm NUMERIC(5,2) NULL,
    waist_cm NUMERIC(5,2) NULL,
    arms_cm NUMERIC(5,2) NULL,
    thighs_cm NUMERIC(5,2) NULL,
    notes TEXT NULL,
    recorded_by_user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_metrics_member_date ON member_health_metrics(member_id, recorded_date);

-- 4. Membership Plans
CREATE TABLE IF NOT EXISTS membership_plans (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    description TEXT NULL,
    duration_type VARCHAR(20) DEFAULT 'months' NOT NULL,
    duration_count INT DEFAULT 1 NOT NULL,
    price NUMERIC(10,2) NOT NULL,
    signup_fee NUMERIC(10,2) DEFAULT 0.00 NOT NULL,
    includes_classes BOOLEAN DEFAULT true NOT NULL,
    includes_pt_sessions INT DEFAULT 0 NOT NULL,
    max_freeze_days INT DEFAULT 30 NOT NULL,
    features JSONB NULL,
    badge_color VARCHAR(30) DEFAULT 'primary' NOT NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 5. Member Subscriptions
CREATE TABLE IF NOT EXISTS member_subscriptions (
    id BIGSERIAL PRIMARY KEY,
    member_id BIGINT NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    plan_id BIGINT NOT NULL REFERENCES membership_plans(id) ON DELETE CASCADE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(30) DEFAULT 'active' NOT NULL,
    price_paid NUMERIC(10,2) NOT NULL,
    remaining_pt_sessions INT DEFAULT 0 NOT NULL,
    is_auto_renew BOOLEAN DEFAULT false NOT NULL,
    frozen_at TIMESTAMP WITH TIME ZONE NULL,
    unfreeze_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_sub_member_status ON member_subscriptions(member_id, status);
CREATE INDEX IF NOT EXISTS idx_sub_end_status ON member_subscriptions(end_date, status);

-- 6. Attendances
CREATE TABLE IF NOT EXISTS attendances (
    id BIGSERIAL PRIMARY KEY,
    member_id BIGINT NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    check_in_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    check_out_time TIMESTAMP WITH TIME ZONE NULL,
    check_in_method VARCHAR(30) DEFAULT 'front_desk' NOT NULL,
    checked_in_by_user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_att_member_checkin ON attendances(member_id, check_in_time);
CREATE INDEX IF NOT EXISTS idx_att_times ON attendances(check_in_time, check_out_time);

-- 7. Trainers
CREATE TABLE IF NOT EXISTS trainers (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    specialties JSONB NULL,
    biography TEXT NULL,
    hourly_rate NUMERIC(8,2) DEFAULT 40.00 NOT NULL,
    commission_rate_percentage NUMERIC(5,2) DEFAULT 20.00 NOT NULL,
    certifications TEXT NULL,
    is_available BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 8. Trainer Member Assignments
CREATE TABLE IF NOT EXISTS trainer_member_assignments (
    id BIGSERIAL PRIMARY KEY,
    trainer_id BIGINT NOT NULL REFERENCES trainers(id) ON DELETE CASCADE,
    member_id BIGINT NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    package_name VARCHAR(120) DEFAULT 'Standard PT Coaching' NOT NULL,
    total_sessions INT DEFAULT 10 NOT NULL,
    completed_sessions INT DEFAULT 0 NOT NULL,
    status VARCHAR(30) DEFAULT 'active' NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 9. Fitness Classes
CREATE TABLE IF NOT EXISTS fitness_classes (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    description TEXT NULL,
    trainer_id BIGINT NULL REFERENCES trainers(id) ON DELETE SET NULL,
    room VARCHAR(100) DEFAULT 'Main Studio A' NOT NULL,
    capacity INT DEFAULT 25 NOT NULL,
    day_of_week VARCHAR(20) DEFAULT 'Monday' NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    difficulty_level VARCHAR(30) DEFAULT 'all_levels' NOT NULL,
    color_tag VARCHAR(20) DEFAULT '#10b981' NOT NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 10. Class Bookings
CREATE TABLE IF NOT EXISTS class_bookings (
    id BIGSERIAL PRIMARY KEY,
    class_id BIGINT NOT NULL REFERENCES fitness_classes(id) ON DELETE CASCADE,
    member_id BIGINT NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    booking_date DATE NOT NULL,
    status VARCHAR(30) DEFAULT 'booked' NOT NULL,
    attended_at TIMESTAMP WITH TIME ZONE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_class_member_date UNIQUE (class_id, member_id, booking_date)
);

-- 11. Workout Plans
CREATE TABLE IF NOT EXISTS workout_plans (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    trainer_id BIGINT NULL REFERENCES trainers(id) ON DELETE SET NULL,
    member_id BIGINT NULL REFERENCES members(id) ON DELETE CASCADE,
    difficulty_level VARCHAR(30) DEFAULT 'intermediate' NOT NULL,
    days_per_week INT DEFAULT 4 NOT NULL,
    routine_schedule JSONB NULL,
    is_template BOOLEAN DEFAULT false NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 12. Diet Plans
CREATE TABLE IF NOT EXISTS diet_plans (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    trainer_id BIGINT NULL REFERENCES trainers(id) ON DELETE SET NULL,
    member_id BIGINT NULL REFERENCES members(id) ON DELETE CASCADE,
    target_calories INT DEFAULT 2500 NOT NULL,
    protein_grams INT DEFAULT 180 NOT NULL,
    carbs_grams INT DEFAULT 250 NOT NULL,
    fats_grams INT DEFAULT 70 NOT NULL,
    meals JSONB NULL,
    is_template BOOLEAN DEFAULT false NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 13. Products
CREATE TABLE IF NOT EXISTS products (
    id BIGSERIAL PRIMARY KEY,
    sku VARCHAR(60) UNIQUE NOT NULL,
    barcode VARCHAR(100) NULL,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(50) DEFAULT 'supplement' NOT NULL,
    description TEXT NULL,
    cost_price NUMERIC(10,2) DEFAULT 0.00 NOT NULL,
    selling_price NUMERIC(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0 NOT NULL,
    low_stock_threshold INT DEFAULT 5 NOT NULL,
    image VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 14. Invoices
CREATE TABLE IF NOT EXISTS invoices (
    id BIGSERIAL PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    member_id BIGINT NULL REFERENCES members(id) ON DELETE SET NULL,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    customer_name VARCHAR(255) NULL,
    subtotal NUMERIC(10,2) NOT NULL,
    tax_amount NUMERIC(10,2) DEFAULT 0.00 NOT NULL,
    discount_amount NUMERIC(10,2) DEFAULT 0.00 NOT NULL,
    total_amount NUMERIC(10,2) NOT NULL,
    payment_status VARCHAR(30) DEFAULT 'paid' NOT NULL,
    payment_method VARCHAR(30) DEFAULT 'cash' NOT NULL,
    invoice_type VARCHAR(30) DEFAULT 'pos_retail' NOT NULL,
    paid_at TIMESTAMP WITH TIME ZONE NULL,
    due_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 15. Invoice Items
CREATE TABLE IF NOT EXISTS invoice_items (
    id BIGSERIAL PRIMARY KEY,
    invoice_id BIGINT NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
    itemable_type VARCHAR(255) NULL,
    itemable_id BIGINT NULL,
    item_description VARCHAR(200) NOT NULL,
    unit_price NUMERIC(10,2) NOT NULL,
    quantity INT DEFAULT 1 NOT NULL,
    total_price NUMERIC(10,2) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 16. Lockers
CREATE TABLE IF NOT EXISTS lockers (
    id BIGSERIAL PRIMARY KEY,
    locker_number VARCHAR(30) UNIQUE NOT NULL,
    zone VARCHAR(80) DEFAULT 'General Floor' NOT NULL,
    status VARCHAR(30) DEFAULT 'available' NOT NULL,
    assigned_member_id BIGINT NULL REFERENCES members(id) ON DELETE SET NULL,
    assigned_at TIMESTAMP WITH TIME ZONE NULL,
    expires_at TIMESTAMP WITH TIME ZONE NULL,
    rental_fee_monthly NUMERIC(8,2) DEFAULT 15.00 NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 17. Equipment
CREATE TABLE IF NOT EXISTS equipment (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(50) DEFAULT 'cardio' NOT NULL,
    serial_number VARCHAR(100) NULL,
    location VARCHAR(100) DEFAULT 'Main Fitness Floor' NOT NULL,
    purchase_date DATE NULL,
    warranty_expiry DATE NULL,
    last_service_date DATE NULL,
    next_service_due DATE NULL,
    status VARCHAR(30) DEFAULT 'operational' NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 18. Gym Settings
CREATE TABLE IF NOT EXISTS gym_settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(100) UNIQUE NOT NULL,
    value JSONB NULL,
    group_name VARCHAR(50) DEFAULT 'general' NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Initial Base Settings
INSERT INTO gym_settings (key, value, group_name, description) VALUES
('gym_name', '"PREMIER FITNESS CLUB"', 'general', 'Club branding title'),
('tax_rate_percentage', '8.5', 'billing', 'Sales tax percentage'),
('currency_symbol', '"$"', 'billing', 'Primary currency symbol')
ON CONFLICT (key) DO NOTHING;
