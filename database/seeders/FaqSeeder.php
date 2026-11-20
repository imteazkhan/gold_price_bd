<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            ['How are gold prices determined in Bangladesh?', 'BAJUS adjusts rates based on international spot prices (LBMA), USD/BDT exchange rate, and local supply. Our page updates live after official announcements.'],
            ['1 vhori equals how many grams?', '1 vhori (bhori) = 11.664 grams exactly. It\'s further divided into 16 ana, 1 ana = 6 roti.'],
            ['Why is gold cheaper in Dubai?', 'Gulf countries charge little to no import duty on bullion, while Bangladesh applies import duty + VAT, making local gold 12–18% more expensive.'],
            ['What is a typical making charge?', 'Plain ornaments: ৳300–500/g. Intricate bridal designs: 6–12% of gold value. Always confirm before purchase.'],
        ];

        foreach ($faqs as $i => [$q, $a]) {
            Faq::updateOrCreate(['question' => $q], ['answer' => $a, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
