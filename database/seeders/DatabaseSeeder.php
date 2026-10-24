<?php

namespace Database\Seeders;

use App\Models\AgencyPartner;
use App\Models\BuyerLead;
use App\Models\Commission;
use App\Models\DigitalAkad;
use App\Models\PropertyFile;
use App\Models\PropertyProject;
use App\Models\User;
use App\Support\CommissionSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin User (1 record)
        $admin = User::create([
            'name' => 'Super Admin Syariah',
            'email' => 'admin@galerypropertysyariah.com',
            'phone' => '081100000001',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'remember_token' => Str::random(10),
        ]);

        // 2. Seed Agency Partners (5 records with corresponding users)
        $agencyNames = [
            'PT Berkah Properti Syariah',
            'Amanah Islamic Land Agency',
            'Hijrah Propertindo Utama',
            'Darussalam Realty Syariah',
            'Mitra Hasanah Property',
        ];

        $banks = ['Bank Syariah Indonesia (BSI)', 'Bank Muamalat', 'BCA Syariah'];
        $agencies = collect();

        foreach ($agencyNames as $idx => $agencyName) {
            $agentUser = User::create([
                'name' => 'Ustadz / Agen Mitra ' . ($idx + 1),
                'email' => 'agency' . ($idx + 1) . '@galerypropertysyariah.com',
                'phone' => '0812' . str_pad((string)($idx + 1), 8, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'agency_partner',
                'status' => 'active',
                'remember_token' => Str::random(10),
            ]);

            $agency = AgencyPartner::create([
                'user_id' => $agentUser->id,
                'agency_name' => $agencyName,
                'license_number' => 'SIU-P4/' . (100 + $idx) . '/APS/2026',
                'company_address' => 'Jl. Bisnis Syariah No. ' . ($idx * 5 + 10) . ', Jakarta Selatan',
                'bank_name' => $banks[$idx % count($banks)],
                'bank_account_number' => '71' . str_pad((string)($idx + 100), 8, '5', STR_PAD_LEFT),
                'bank_account_holder' => $agencyName,
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]);

            $agencies->push($agency);
        }

        // 3. Seed Property Projects (10 records)
        $projectTemplates = [
            [
                'title' => 'Cluster Madinah Green Village Phase 1',
                'type' => 'rumah',
                'scheme' => 'istishna',
                'price' => 750000000.00,
                'comm_rate' => 2.50,
                'province' => 'Jawa Barat',
                'city' => 'Bogor',
                'district' => 'Sukaraja',
                'bedroom' => 3,
                'bathroom' => 2,
                'lb' => 60,
                'lt' => 84,
                'dev' => 'PT Madinah Propertindo Syariah',
            ],
            [
                'title' => 'Cluster Al-Fatih Residence Depok',
                'type' => 'rumah',
                'scheme' => 'murabahah',
                'price' => 890000000.00,
                'comm_rate' => 2.50,
                'province' => 'Jawa Barat',
                'city' => 'Depok',
                'district' => 'Sawangan',
                'bedroom' => 3,
                'bathroom' => 2,
                'lb' => 70,
                'lt' => 90,
                'dev' => 'PT Hijrah Land Development',
            ],
            [
                'title' => 'Kavling Sharia Sentul Hills View',
                'type' => 'tanah_kavling',
                'scheme' => 'cash_bertahap',
                'price' => 350000000.00,
                'comm_rate' => 3.00,
                'province' => 'Jawa Barat',
                'city' => 'Bogor',
                'district' => 'Babakan Madang',
                'bedroom' => null,
                'bathroom' => null,
                'lb' => null,
                'lt' => 120,
                'dev' => 'PT Sharia Agro Mandiri',
            ],
            [
                'title' => 'Griya Sakinah Islamic Village Bekasi',
                'type' => 'rumah',
                'scheme' => 'istishna',
                'price' => 620000000.00,
                'comm_rate' => 2.50,
                'province' => 'Jawa Barat',
                'city' => 'Bekasi',
                'district' => 'Mustika Jaya',
                'bedroom' => 2,
                'bathroom' => 1,
                'lb' => 45,
                'lt' => 72,
                'dev' => 'PT Amanah Griya Sejahtera',
            ],
            [
                'title' => 'Baitul Jannati Harmoni Pamulang',
                'type' => 'rumah',
                'scheme' => 'cash_keras',
                'price' => 980000000.00,
                'comm_rate' => 2.00,
                'province' => 'Banten',
                'city' => 'Tangerang Selatan',
                'district' => 'Pamulang',
                'bedroom' => 4,
                'bathroom' => 3,
                'lb' => 90,
                'lt' => 105,
                'dev' => 'PT Madani Graha Utama',
            ],
            [
                'title' => 'Ruko Syariah Al-Barokah Boulevard Bandung',
                'type' => 'ruko',
                'scheme' => 'murabahah',
                'price' => 1450000000.00,
                'comm_rate' => 3.00,
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Cimenyan',
                'bedroom' => 2,
                'bathroom' => 2,
                'lb' => 120,
                'lt' => 75,
                'dev' => 'PT Barokah Usaha Mandiri',
            ],
            [
                'title' => 'Cluster Firdaus Hills Ciampea Bogor',
                'type' => 'rumah',
                'scheme' => 'istishna',
                'price' => 520000000.00,
                'comm_rate' => 2.50,
                'province' => 'Jawa Barat',
                'city' => 'Bogor',
                'district' => 'Ciampea',
                'bedroom' => 2,
                'bathroom' => 1,
                'lb' => 38,
                'lt' => 66,
                'dev' => 'PT Madinah Propertindo Syariah',
            ],
            [
                'title' => 'De Medina Eco Green Cimanggis',
                'type' => 'rumah',
                'scheme' => 'cash_bertahap',
                'price' => 1150000000.00,
                'comm_rate' => 2.50,
                'province' => 'Jawa Barat',
                'city' => 'Depok',
                'district' => 'Cimanggis',
                'bedroom' => 3,
                'bathroom' => 2,
                'lb' => 85,
                'lt' => 110,
                'dev' => 'PT Hijrah Land Development',
            ],
            [
                'title' => 'Kavling Siap Bangun Taman Surga Serpong',
                'type' => 'tanah_kavling',
                'scheme' => 'cash_keras',
                'price' => 480000000.00,
                'comm_rate' => 3.50,
                'province' => 'Banten',
                'city' => 'Tangerang Selatan',
                'district' => 'Serpong',
                'bedroom' => null,
                'bathroom' => null,
                'lb' => null,
                'lt' => 100,
                'dev' => 'PT Madani Graha Utama',
            ],
            [
                'title' => 'Al-Ihsan Heritage Residence Buahbatu',
                'type' => 'rumah',
                'scheme' => 'murabahah',
                'price' => 820000000.00,
                'comm_rate' => 2.50,
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Buahbatu',
                'bedroom' => 3,
                'bathroom' => 2,
                'lb' => 65,
                'lt' => 88,
                'dev' => 'PT Barokah Usaha Mandiri',
            ],
        ];

        $projects = collect();

        foreach ($projectTemplates as $i => $item) {
            $assignedAgency = $agencies[$i % $agencies->count()];
            $slug = Str::slug($item['title']);

            $project = PropertyProject::create([
                'agency_partner_id' => $assignedAgency->id,
                'title' => $item['title'],
                'slug' => $slug,
                'developer_name' => $item['dev'],
                'property_type' => $item['type'],
                'syariah_scheme' => $item['scheme'],
                'price' => $item['price'],
                'commission_rate' => $item['comm_rate'],
                'province' => $item['province'],
                'city' => $item['city'],
                'district' => $item['district'],
                'address_detail' => 'Kawasan Hunian Islami, Jl. Utama No. ' . ($i + 1) . ', ' . $item['city'],
                'bedroom' => $item['bedroom'],
                'bathroom' => $item['bathroom'],
                'building_area' => $item['lb'],
                'land_area' => $item['lt'],
                'description' => 'Lingkungan perumahan ramah anak dan keluarga muslim, berjarak 200m dari masjid jami serta sarana tahfidz qur\'an.',
                'halal_guarantee_notes' => '100% Akad Murni Syariah: Tanpa Riba, Tanpa Denda Keterlambatan, Tanpa Sita Paksa, Tanpa Asuransi Non-Halal.',
                'status' => 'available',
                'is_verified' => true,
            ]);

            $projects->push($project);

            // 4. Seed Property Files (3 files per project = 30 records total)
            PropertyFile::create([
                'property_project_id' => $project->id,
                'file_path' => 'properties/photos/fasad-project-' . ($i + 1) . '.jpg',
                'file_type' => 'photo',
                'caption' => 'Foto Utama Tampak Fasad Depan Modern Minimalis',
                'is_primary' => true,
            ]);

            PropertyFile::create([
                'property_project_id' => $project->id,
                'file_path' => 'properties/siteplans/siteplan-' . ($i + 1) . '.png',
                'file_type' => 'siteplan',
                'caption' => 'Denah Tata Ruang & Posisi Unit Terhadap Arah Kiblat',
                'is_primary' => false,
            ]);

            PropertyFile::create([
                'property_project_id' => $project->id,
                'file_path' => 'properties/legal/fatwa-syariah-' . ($i + 1) . '.pdf',
                'file_type' => 'legal_doc',
                'caption' => 'Dokumen Fatwa & Sertifikat Kesesuaian Syariah Pengembang',
                'is_primary' => false,
            ]);
        }

        // 5. Seed Buyer Leads (15 records with associated buyer users)
        $buyerNames = [
            'Fulan bin Abdullah', 'Aisyah Humaira', 'Ibrahim Al-Faruq', 'Khadijah Zahra',
            'Zaid bin Tsabit', 'Maryam Ulfah', 'Umar Al-Khattab', 'Fathimah Az-Zahra',
            'Ali bin Abi Thalib', 'Salma Karimah', 'Hamzah Asadullah', 'Zubair bin Awwam',
            'Bilal bin Rabah', 'Ruqayyah binti Muhammad', 'Thalhah bin Ubaidillah',
        ];

        $buyerLeads = collect();

        foreach ($buyerNames as $idx => $bName) {
            $bEmail = 'pembeli' . ($idx + 1) . '@keluargamuslim.com';

            $buyerUser = User::create([
                'name' => $bName,
                'email' => $bEmail,
                'phone' => '0878' . str_pad((string)($idx + 1), 8, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'buyer',
                'status' => 'active',
                'remember_token' => Str::random(10),
            ]);

            $assignedProject = $projects[$idx % $projects->count()];
            $assignedAgency = $assignedProject->agencyPartner;

            $leadStatuses = ['new_lead', 'survey_scheduled', 'booking_prospect', 'akad_process', 'closed'];
            $leadStatus = $leadStatuses[$idx % count($leadStatuses)];

            $lead = BuyerLead::create([
                'user_id' => $buyerUser->id,
                'agency_partner_id' => $assignedAgency->id,
                'property_project_id' => $assignedProject->id,
                'full_name' => $bName,
                'phone' => $buyerUser->phone,
                'email' => $bEmail,
                'budget_min' => $assignedProject->price * 0.85,
                'budget_max' => $assignedProject->price * 1.15,
                'preferred_location' => $assignedProject->city,
                'preferred_scheme' => $assignedProject->syariah_scheme,
                'status' => $leadStatus,
                'notes' => 'Keluarga muslim dengan 2 anak, memprioritaskan unit ramah anak dan akad 100% syariah.',
            ]);

            $buyerLeads->push($lead);
        }

        // 6. Seed Digital Akads (6 records) & 7. Seed Commissions per termin (4 akad jual beli × 2 termin = 8 records)
        $akadTypes = ['akad_istishna', 'akad_murabahah', 'mou_peminatan'];

        for ($k = 0; $k < 6; $k++) {
            $lead = $buyerLeads[$k];
            $project = $lead->propertyProject;
            $agency = $project->agencyPartner;

            $transAmount = $project->price;
            $dp = $transAmount * 0.20;
            $akadType = $akadTypes[$k % count($akadTypes)];
            $akadNum = 'AKAD/' . date('Ym') . '/' . str_pad((string)($k + 1), 4, '0', STR_PAD_LEFT);

            $akad = DigitalAkad::create([
                'akad_number' => $akadNum,
                'property_project_id' => $project->id,
                'buyer_lead_id' => $lead->id,
                'agency_partner_id' => $agency->id,
                'akad_type' => $akadType,
                'transaction_amount' => $transAmount,
                'dp_amount' => $dp,
                'tenor_months' => 60,
                'terms_content' => 'Bismillahir Rahmanir Rahim. Kedua belah pihak bersepakat mengikat diri dalam ' . strtoupper(str_replace('_', ' ', $akadType)) . ' atas unit ' . $project->title . ' senilai Rp ' . number_format($transAmount, 0, ',', '.') . ' dengan DP Rp ' . number_format($dp, 0, ',', '.') . '. Akad mengikat secara syariah, tanpa denda keterlambatan dan tanpa sita sepihak.',
                'document_pdf_path' => 'documents/akads/' . strtolower(str_replace('/', '-', $akadNum)) . '.pdf',
                'buyer_signed_at' => now()->subDays(5 - $k),
                'agency_signed_at' => now()->subDays(5 - $k),
                'developer_signed_at' => now()->subDays(5 - $k),
                'status' => 'signed',
            ]);

            // Komisi terbit otomatis per termin untuk akad jual beli (MOU tidak menghasilkan komisi)
            $termins = CommissionSchedule::createForAkad($akad, $admin);
            $this->advanceFirstTermin($termins->first(), $k, $admin);
        }
    }

    /**
     * Majukan termin 1 ke berbagai status agar semua tahap state machine komisi terlihat saat demo.
     */
    private function advanceFirstTermin(?Commission $termin, int $k, User $admin): void
    {
        if (! $termin) {
            return;
        }

        // k=0: selesai, k=1: dana diterima, k=3: invoice lewat jatuh tempo, k=4: jatuh tempo
        $target = [0 => Commission::STATUS_SETTLED, 1 => Commission::STATUS_PAID, 3 => Commission::STATUS_INVOICED, 4 => Commission::STATUS_DUE][$k] ?? null;
        $order = [Commission::STATUS_DUE, Commission::STATUS_INVOICED, Commission::STATUS_PAID, Commission::STATUS_SETTLED];

        foreach ($order as $status) {
            if ($target === null || array_search($status, $order) > array_search($target, $order)) {
                break;
            }

            $invoicedAt = $k === 3 ? now()->subDays(20) : now()->subDays(6);

            match ($status) {
                Commission::STATUS_DUE => $termin->transitionTo($status, $admin, 'DP lunas, kuitansi developer terlampir.'),
                Commission::STATUS_INVOICED => $termin->transitionTo($status, $admin, 'Invoice diterbitkan.', [
                    'invoice_number' => 'INV/' . $invoicedAt->format('Ym') . '/' . str_pad((string) ($k + 1), 4, '0', STR_PAD_LEFT),
                    'invoiced_at' => $invoicedAt,
                    'due_at' => $invoicedAt->copy()->addDays((int) config('commission.invoice_due_days'))->endOfDay(),
                ]),
                Commission::STATUS_PAID => $termin->transitionTo($status, $admin, 'Dana komisi dari developer diterima.', [
                    'paid_at' => now()->subDays(2),
                    'payment_proof_path' => 'receipts/commissions/developer-transfer-' . ($k + 1) . '.jpg',
                ]),
                Commission::STATUS_SETTLED => $termin->transitionTo($status, $admin, 'Bagian agency diteruskan.', [
                    'settled_at' => now()->subDay(),
                    'payout_proof_path' => 'receipts/payouts/bsi-transfer-' . ($k + 1) . '.jpg',
                ]),
            };
        }
    }
}
