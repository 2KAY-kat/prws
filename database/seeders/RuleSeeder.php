<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // Legal
            ['rule_id' => 'LEG-001', 'name' => 'Privacy Policy Present', 'category' => 'Legal', 'severity' => 'Critical', 'points' => 50],
            ['rule_id' => 'LEG-002', 'name' => 'Terms of Service Present', 'category' => 'Legal', 'severity' => 'High', 'points' => 50],
            ['rule_id' => 'LEG-003', 'name' => 'Cookie Policy Present', 'category' => 'Legal', 'severity' => 'Medium', 'points' => 25],
            ['rule_id' => 'LEG-004', 'name' => 'Copyright Information', 'category' => 'Legal', 'severity' => 'Low', 'points' => 25],

            // Contact
            ['rule_id' => 'CON-001', 'name' => 'Contact Page Exists', 'category' => 'Contact', 'severity' => 'Medium', 'points' => 25],
            ['rule_id' => 'CON-002', 'name' => 'Support Email Exists', 'category' => 'Contact', 'severity' => 'Medium', 'points' => 25],
            ['rule_id' => 'CON-003', 'name' => 'Organization Information', 'category' => 'Contact', 'severity' => 'Low', 'points' => 25],

            // Reliability
            ['rule_id' => 'REL-001', 'name' => 'Custom 404 Page', 'category' => 'Reliability', 'severity' => 'Medium', 'points' => 40],
            ['rule_id' => 'REL-004', 'name' => 'Favicon Exists', 'category' => 'Reliability', 'severity' => 'Low', 'points' => 10],

            // SEO
            ['rule_id' => 'SEO-001', 'name' => 'robots.txt', 'category' => 'SEO', 'severity' => 'Medium', 'points' => 20],
            ['rule_id' => 'SEO-002', 'name' => 'sitemap.xml', 'category' => 'SEO', 'severity' => 'Medium', 'points' => 20],
            ['rule_id' => 'SEO-003', 'name' => 'Title Tag', 'category' => 'SEO', 'severity' => 'Low', 'points' => 10],
            ['rule_id' => 'SEO-004', 'name' => 'Meta Description', 'category' => 'SEO', 'severity' => 'Low', 'points' => 10],

            // Accessibility
            ['rule_id' => 'ACC-003', 'name' => 'Semantic HTML', 'category' => 'Accessibility', 'severity' => 'Low', 'points' => 30],
            ['rule_id' => 'ACC-004', 'name' => 'Language Declaration', 'category' => 'Accessibility', 'severity' => 'Low', 'points' => 20],

            // Security
            ['rule_id' => 'SEC-001', 'name' => 'HTTPS Enabled', 'category' => 'Security', 'severity' => 'Critical', 'points' => 100],
            ['rule_id' => 'SEC-002', 'name' => 'HSTS Enabled', 'category' => 'Security', 'severity' => 'High', 'points' => 30],
            ['rule_id' => 'SEC-004', 'name' => 'X-Frame-Options', 'category' => 'Security', 'severity' => 'Medium', 'points' => 20],
            ['rule_id' => 'SEC-005', 'name' => 'X-Content-Type-Options', 'category' => 'Security', 'severity' => 'Medium', 'points' => 20],
            ['rule_id' => 'SEC-006', 'name' => 'Referrer Policy', 'category' => 'Security', 'severity' => 'Medium', 'points' => 20],
        ];

        foreach ($rules as $rule) {
            Rule::updateOrCreate(['rule_id' => $rule['rule_id']], $rule);
        }
    }
}
