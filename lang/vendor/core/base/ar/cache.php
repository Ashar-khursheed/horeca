<?php

return [
    'cache_management' => 'Cache management',
    'cache_management_description' => 'امسح ذاكرة التخزين المؤقت لتحديث موقعك.',
    'cache_commands' => 'مسح أوامر ذاكرة التخزين المؤقت',
    'commands' => [
        'clear_cms_cache' => [
            'title' => 'مسح كافة ذاكرة التخزين المؤقت لنظام إدارة المحتوى (CMS).',
            'description' => 'مسح التخزين المؤقت لـ CMS: التخزين المؤقت لقاعدة البيانات، والكتل الثابتة... قم بتشغيل هذا الأمر عندما لا ترى التغييرات بعد تحديث البيانات.',
            'success_msg' => 'تم تنظيف ذاكرة التخزين المؤقت',
        ],
        'refresh_compiled_views' => [
            'title' => 'تحديث طرق العرض المجمعة',
            'description' => 'مسح طرق العرض المجمعة لجعل طرق العرض محدثة.',
            'success_msg' => 'تم تحديث عرض ذاكرة التخزين المؤقت',
        ],
        'clear_config_cache' => [
            'title' => 'مسح ذاكرة التخزين المؤقت للتكوين',
            'description' => 'قد تحتاج إلى تحديث التخزين المؤقت للتكوين عندما تقوم بتغيير شيء ما في بيئة الإنتاج.',
            'success_msg' => 'تم تنظيف ذاكرة التخزين المؤقت للتكوين',
        ],
        'clear_route_cache' => [
            'title' => 'مسح ذاكرة التخزين المؤقت للطريق',
            'description' => 'مسح توجيه ذاكرة التخزين المؤقت.',
            'success_msg' => 'تم تنظيف ذاكرة التخزين المؤقت للطريق',
        ],
        'clear_log' => [
            'title' => 'سجل نظيف',
            'description' => 'مسح ملفات سجل النظام',
            'success_msg' => 'تم تنظيف سجل النظام',
        ],
    ],
];
