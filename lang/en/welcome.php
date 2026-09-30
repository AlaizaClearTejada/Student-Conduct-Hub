<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Welcome / Landing Page Translations — English
    |--------------------------------------------------------------------------
    */

    // Navigation
    'nav' => [
        'features' => 'Features',
        'faq' => 'FAQ',
        'support' => 'Support',
        'login' => 'Login',
        'dashboard' => 'Dashboard',
    ],

    // Hero
    'hero' => [
        'badge' => 'Secure Institutional Access',
        'title_line1' => 'Student Conduct',
        'title_line2' => 'Management System',
        'tagline' => 'Promoting Accountability, Consistency, and Student Growth through Digital Integrity',
        'stat_resolution' => '98%',
        'label_resolution' => 'Resolution Rate',
        'stat_consistency' => '100%',
        'label_consistency' => 'Policy Consistency',
        'stat_transparency' => 'Real-time',
        'label_transparency' => 'Transparency',
        'badge_ssl' => 'SSL Encrypted',
        'badge_mfa' => 'MFA Protected',
        'badge_privacy' => 'Privacy Compliant',
    ],

    // Features section
    'features' => [
        'title' => 'System Features',
        'subtitle' => 'Comprehensive tools for modern conduct management',
        'automation' => [
            'title' => 'Automated Workflows',
            'desc' => 'Streamline incident reporting and resolution with intelligent automation.',
        ],
        'transparency' => [
            'title' => 'Complete Transparency',
            'desc' => 'Full visibility into conduct records for all stakeholders with proper permissions.',
        ],
        'analytics' => [
            'title' => 'Advanced Analytics',
            'desc' => 'Data-driven insights to improve institutional conduct policies.',
        ],
        'security' => [
            'title' => 'Enterprise Security',
            'desc' => 'Multi-factor authentication and encrypted data transmission.',
        ],
        'notifications' => [
            'title' => 'Real-time Notifications',
            'desc' => 'Stay updated with instant alerts on conduct-related events.',
        ],
        'audit' => [
            'title' => 'Complete Audit Trail',
            'desc' => 'Track every action with comprehensive logging and reporting.',
        ],
    ],

    // Announcements & Quick Links
    'announcements' => [
        'title' => 'System Announcements',
        'maintenance' => [
            'title' => 'System Maintenance',
            'desc' => 'Scheduled maintenance on Saturday, 3:00 AM - 5:00 AM',
        ],
        'policy' => [
            'title' => 'Policy Update',
            'desc' => 'Updated Student Conduct Guidelines now available in the handbook',
        ],
        'feature' => [
            'title' => 'New Feature',
            'desc' => 'Mobile app now available for iOS and Android devices',
        ],
    ],

    'quick_links' => [
        'title' => 'Quick Links',
        'handbook' => 'Digital Student Handbook',
        'privacy' => 'Privacy Policy',
        'report' => 'How to Report an Incident',
        'faqs' => 'FAQs & Help Center',
    ],

    // FAQ section
    'faq' => [
        'title' => 'Frequently Asked Questions',
        'subtitle' => 'Common questions about the Student Conduct Management System',
        'items' => [
            ['q' => 'Who can see my conduct records?',
                'a' => 'Records are strictly confidential. Only authorized OSDW Administrators and the specific student can view full history. Faculty members can only view incidents they reported. The system maintains a complete audit trail of all access for security purposes.'],
            ['q' => 'How does "Progressive Sanctioning" work?',
                'a' => 'The system automatically calculates penalties based on the frequency and severity of offenses as defined in the CSU Student Handbook. If a minor offense is repeated, the system suggests the next level of disciplinary action to ensure consistency and fairness across all cases.'],
            ['q' => 'Can I appeal a recorded violation?',
                'a' => 'Yes. All students have a "Request Appeal" button on their portal which initiates a formal review by the OSDW. You will be notified of the appeal status and can submit supporting documentation throughout the process. Appeals are reviewed within 5 business days.'],
            ['q' => 'What if I received a wrong notification?',
                'a' => 'Contact the OSDW support team immediately at scms@csu.edu.ph or call +63 (078) 304-1234. You can also use the in-system messaging feature to notify the reporting faculty member or administrator. All entries are reviewed before becoming permanent records.'],
            ['q' => 'How secure is my conduct data?',
                'a' => 'All data is encrypted using industry-standard SSL (Secure Sockets Layer) technology. The system requires Multi-Factor Authentication (MFA) for all administrative roles. Regular security audits are conducted, and all database access is logged and monitored to ensure institutional privacy standards.'],
            ['q' => 'How long are conduct records kept?',
                'a' => 'Conduct records are maintained throughout your enrollment at CSU and for 5 years after graduation, as per university policy. Minor violations may be expunged after a clean conduct period, as defined in the Student Handbook. Contact OSDW for specific retention questions.'],
        ],
    ],

    // Support section
    'support' => [
        'title' => 'Need Assistance?',
        'subtitle' => 'Our dedicated support team is here to help you',
        'email' => [
            'label' => 'Email Support',
            'value' => 'scms@csu.edu.ph',
        ],
        'phone' => [
            'label' => 'Phone Support',
            'value' => '+63 (078) 304-1234',
        ],
        'hours' => [
            'label' => 'Office Hours',
            'value' => 'Mon-Fri: 8:00 AM - 5:00 PM',
        ],
    ],

    // Security notice
    'security_notice' => [
        'title' => 'Security Reminder',
        'desc' => 'When using public terminals (e.g., CICS computer labs), always log out after completing your session to protect your conduct information.',
        'dismiss' => 'Got it',
    ],

    // Footer
    'footer' => [
        'system_name' => 'Student Conduct Management System',
        'university' => 'Cagayan State University - CICS',
        'tagline' => 'Promoting accountability, consistency, and student growth through digital integrity.',
        'resources' => 'Resources',
        'handbook' => 'Student Handbook',
        'policies' => 'Conduct Policies',
        'appeal' => 'Appeal Process',
        'faqs' => 'FAQs',
        'legal' => 'Legal',
        'privacy' => 'Privacy Policy',
        'terms' => 'Terms of Service',
        'data' => 'Data Protection',
        'accessibility' => 'Accessibility',
        'copyright' => '© :year Cagayan State University - CICS. All rights reserved.',
        'version' => 'Version 1.0',
        'online' => 'System Online',
    ],
];
