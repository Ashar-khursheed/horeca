<?php

return [
    'settings' => [
        'title' => 'تحسين',
        'description' => 'تصغير مخرجات HTML، وCSS المضمنة، وإزالة التعليقات...',
        'enable' => 'هل تريد تمكين تحسين سرعة الصفحة؟',
    ],
    'collapse_white_space' => 'طي المساحة البيضاء',
    'collapse_white_space_description' => 'يعمل هذا المرشح على تقليل وحدات البايت المنقولة في ملف HTML عن طريق إزالة المسافات البيضاء غير الضرورية.',
    'elide_attributes' => 'حذف الصفات',
    'elide_attributes_description' => 'يعمل هذا المرشح على تقليل حجم نقل ملفات HTML عن طريق إزالة السمات من العلامات عندما تكون القيمة المحددة مساوية للقيمة الافتراضية لتلك السمة. يمكن أن يؤدي ذلك إلى حفظ عدد بسيط من البايتات، وقد يجعل المستند أكثر قابلية للضغط من خلال تحديد العلامات المتأثرة.',
    'inline_css' => 'مضمنة CSS',
    'inline_css_description' => 'يقوم هذا المرشح بتحويل سمة "النمط" المضمنة للعلامات إلى فئات عن طريق نقل CSS إلى الرأس.',
    'insert_dns_prefetch' => 'أدخل الجلب المسبق لـ DNS',
    'insert_dns_prefetch_description' => 'يقوم هذا الفلتر بإدخال العلامات في HEAD لتمكين المتصفح من إجراء الجلب المسبق لـ DNS.',
    'remove_comments' => 'إزالة التعليقات',
    'remove_comments_description' => 'يزيل هذا الفلتر تعليقات HTML وJS وCSS. يعمل المرشح على تقليل حجم نقل ملفات HTML عن طريق إزالة التعليقات. اعتمادًا على ملف HTML، يمكن لهذا المرشح أن يقلل بشكل كبير من عدد البايتات المنقولة على الشبكة.',
    'remove_quotes' => 'إزالة علامات الاقتباس',
    'remove_quotes_description' => 'يقوم عامل التصفية هذا بإزالة علامات الاقتباس غير الضرورية من سمات HTML. على الرغم من أن مواصفات HTML المختلفة تتطلب ذلك، إلا أن المتصفحات تسمح بإغفالها عندما تتكون قيمة إحدى السمات من مجموعة فرعية معينة من الأحرف (الأحرف الأبجدية الرقمية وبعض أحرف الترقيم).',
    'defer_javascript' => 'تأجيل جافا سكريبت',
    'defer_javascript_description' => 'يؤجل تنفيذ جافا سكريبت في HTML. إذا لزم الأمر، قم بإلغاء التأجيل في بعض البرامج النصية، استخدم data-pagespeed-no-defer كسمة برنامج نصي لإلغاء التأجيل.',
];
