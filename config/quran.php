<?php

/*
 * Reciter catalog + Quran radio config.
 *
 * Audio is FULL-SURAH files (one MP3 per surah) from cdn.mp3quran.net —
 * all 12 servers verified serving surahs 1/50/114 during the build.
 * The stream plays one surah at a time on a global clock; durations come
 * from HEAD content-length ÷ bitrate (CBR 128k) and are cached in the
 * surah_durations table (php artisan quran:warm-durations).
 */

return [

    'reciters' => [
        [
            'id' => 'alafasy',
            'name' => 'Mishary Rashid Alafasy',
            'ar' => 'مشاري راشد العفاسي',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/mishary-alafasy/r1/'],
            ],
        ],
        [
            'id' => 'abdulbasit',
            'name' => 'Abdul Basit Abdus-Samad',
            'ar' => 'عبد الباسط عبد الصمد',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/abdulbasit-abdulsamad/r1/'],
            ],
        ],
        [
            'id' => 'sudais',
            'name' => 'Abdurrahman As-Sudais',
            'ar' => 'عبد الرحمن السديس',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/abdulrahman-sudais/r1/'],
            ],
        ],
        [
            'id' => 'mahermuaiqly',
            'name' => 'Maher Al Muaiqly',
            'ar' => 'ماهر المعيقلي',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/maher-muaiqly/r3/'],
            ],
        ],
        [
            'id' => 'minshawi',
            'name' => 'Muhammad Siddiq Al-Minshawi',
            'ar' => 'محمد صديق المنشاوي',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/muhammad-minshawi/r4/'],
            ],
        ],
        [
            'id' => 'husary',
            'name' => 'Mahmoud Khalil Al-Husary',
            'ar' => 'محمود خليل الحصري',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/mahmoud-husary/r5/'],
            ],
        ],
        [
            'id' => 'shaatree',
            'name' => 'Abu Bakr Ash-Shatri',
            'ar' => 'أبو بكر الشاطري',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/abubakr-shatri/r1/'],
            ],
        ],
        [
            'id' => 'shuraim',
            'name' => 'Saud Ash-Shuraim',
            'ar' => 'سعود الشريم',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/saud-shuraim/r1/'],
            ],
        ],
        [
            'id' => 'hudhaify',
            'name' => 'Ali Al-Hudhaify',
            'ar' => 'علي الحذيفي',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/ali-hudhaifi/r3/'],
            ],
        ],
        [
            'id' => 'ajamy',
            'name' => 'Ahmad Al-Ajamy',
            'ar' => 'أحمد بن علي العجمي',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/ahmad-ajmi/r1/'],
            ],
        ],
        [
            'id' => 'hanirifai',
            'name' => 'Hani Ar-Rifai',
            'ar' => 'هاني الرفاعي',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/hani-rifai/r1/'],
            ],
        ],
        [
            'id' => 'ayyoub',
            'name' => 'Muhammad Ayyub',
            'ar' => 'محمد أيوب',
            'surah_sources' => [
                ['bitrate' => 128000, 'base' => 'https://cdn.mp3quran.net/audio/muhammad-ayyub/r2/'],
            ],
        ],
    ],

    'total_surahs' => 114,

    // Canonical surah index: Arabic name and ayah count. Baked in so the
    // radio runs even before `php artisan quran:fetch` populates the
    // mushaf metadata files.
    'surahs' => [
        ['id' => 1, 'na' => 'الفاتحة', 'count' => 7],
        ['id' => 2, 'na' => 'البقرة', 'count' => 286],
        ['id' => 3, 'na' => 'آل عمران', 'count' => 200],
        ['id' => 4, 'na' => 'النساء', 'count' => 176],
        ['id' => 5, 'na' => 'المائدة', 'count' => 120],
        ['id' => 6, 'na' => 'الأنعام', 'count' => 165],
        ['id' => 7, 'na' => 'الأعراف', 'count' => 206],
        ['id' => 8, 'na' => 'الأنفال', 'count' => 75],
        ['id' => 9, 'na' => 'التوبة', 'count' => 129],
        ['id' => 10, 'na' => 'يونس', 'count' => 109],
        ['id' => 11, 'na' => 'هود', 'count' => 123],
        ['id' => 12, 'na' => 'يوسف', 'count' => 111],
        ['id' => 13, 'na' => 'الرعد', 'count' => 43],
        ['id' => 14, 'na' => 'ابراهيم', 'count' => 52],
        ['id' => 15, 'na' => 'الحجر', 'count' => 99],
        ['id' => 16, 'na' => 'النحل', 'count' => 128],
        ['id' => 17, 'na' => 'الإسراء', 'count' => 111],
        ['id' => 18, 'na' => 'الكهف', 'count' => 110],
        ['id' => 19, 'na' => 'مريم', 'count' => 98],
        ['id' => 20, 'na' => 'طه', 'count' => 135],
        ['id' => 21, 'na' => 'الأنبياء', 'count' => 112],
        ['id' => 22, 'na' => 'الحج', 'count' => 78],
        ['id' => 23, 'na' => 'المؤمنون', 'count' => 118],
        ['id' => 24, 'na' => 'النور', 'count' => 64],
        ['id' => 25, 'na' => 'الفرقان', 'count' => 77],
        ['id' => 26, 'na' => 'الشعراء', 'count' => 227],
        ['id' => 27, 'na' => 'النمل', 'count' => 93],
        ['id' => 28, 'na' => 'القصص', 'count' => 88],
        ['id' => 29, 'na' => 'العنكبوت', 'count' => 69],
        ['id' => 30, 'na' => 'الروم', 'count' => 60],
        ['id' => 31, 'na' => 'لقمان', 'count' => 34],
        ['id' => 32, 'na' => 'السجدة', 'count' => 30],
        ['id' => 33, 'na' => 'الأحزاب', 'count' => 73],
        ['id' => 34, 'na' => 'سبإ', 'count' => 54],
        ['id' => 35, 'na' => 'فاطر', 'count' => 45],
        ['id' => 36, 'na' => 'يس', 'count' => 83],
        ['id' => 37, 'na' => 'الصافات', 'count' => 182],
        ['id' => 38, 'na' => 'ص', 'count' => 88],
        ['id' => 39, 'na' => 'الزمر', 'count' => 75],
        ['id' => 40, 'na' => 'غافر', 'count' => 85],
        ['id' => 41, 'na' => 'فصلت', 'count' => 54],
        ['id' => 42, 'na' => 'الشورى', 'count' => 53],
        ['id' => 43, 'na' => 'الزخرف', 'count' => 89],
        ['id' => 44, 'na' => 'الدخان', 'count' => 59],
        ['id' => 45, 'na' => 'الجاثية', 'count' => 37],
        ['id' => 46, 'na' => 'الأحقاف', 'count' => 35],
        ['id' => 47, 'na' => 'محمد', 'count' => 38],
        ['id' => 48, 'na' => 'الفتح', 'count' => 29],
        ['id' => 49, 'na' => 'الحجرات', 'count' => 18],
        ['id' => 50, 'na' => 'ق', 'count' => 45],
        ['id' => 51, 'na' => 'الذاريات', 'count' => 60],
        ['id' => 52, 'na' => 'الطور', 'count' => 49],
        ['id' => 53, 'na' => 'النجم', 'count' => 62],
        ['id' => 54, 'na' => 'القمر', 'count' => 55],
        ['id' => 55, 'na' => 'الرحمن', 'count' => 78],
        ['id' => 56, 'na' => 'الواقعة', 'count' => 96],
        ['id' => 57, 'na' => 'الحديد', 'count' => 29],
        ['id' => 58, 'na' => 'المجادلة', 'count' => 22],
        ['id' => 59, 'na' => 'الحشر', 'count' => 24],
        ['id' => 60, 'na' => 'الممتحنة', 'count' => 13],
        ['id' => 61, 'na' => 'الصف', 'count' => 14],
        ['id' => 62, 'na' => 'الجمعة', 'count' => 11],
        ['id' => 63, 'na' => 'المنافقون', 'count' => 11],
        ['id' => 64, 'na' => 'التغابن', 'count' => 18],
        ['id' => 65, 'na' => 'الطلاق', 'count' => 12],
        ['id' => 66, 'na' => 'التحريم', 'count' => 12],
        ['id' => 67, 'na' => 'الملك', 'count' => 30],
        ['id' => 68, 'na' => 'القلم', 'count' => 52],
        ['id' => 69, 'na' => 'الحاقة', 'count' => 52],
        ['id' => 70, 'na' => 'المعارج', 'count' => 44],
        ['id' => 71, 'na' => 'نوح', 'count' => 28],
        ['id' => 72, 'na' => 'الجن', 'count' => 28],
        ['id' => 73, 'na' => 'المزمل', 'count' => 20],
        ['id' => 74, 'na' => 'المدثر', 'count' => 56],
        ['id' => 75, 'na' => 'القيامة', 'count' => 40],
        ['id' => 76, 'na' => 'الانسان', 'count' => 31],
        ['id' => 77, 'na' => 'المرسلات', 'count' => 50],
        ['id' => 78, 'na' => 'النبإ', 'count' => 40],
        ['id' => 79, 'na' => 'النازعات', 'count' => 46],
        ['id' => 80, 'na' => 'عبس', 'count' => 42],
        ['id' => 81, 'na' => 'التكوير', 'count' => 29],
        ['id' => 82, 'na' => 'الإنفطار', 'count' => 19],
        ['id' => 83, 'na' => 'المطففين', 'count' => 36],
        ['id' => 84, 'na' => 'الإنشقاق', 'count' => 25],
        ['id' => 85, 'na' => 'البروج', 'count' => 22],
        ['id' => 86, 'na' => 'الطارق', 'count' => 17],
        ['id' => 87, 'na' => 'الأعلى', 'count' => 19],
        ['id' => 88, 'na' => 'الغاشية', 'count' => 26],
        ['id' => 89, 'na' => 'الفجر', 'count' => 30],
        ['id' => 90, 'na' => 'البلد', 'count' => 20],
        ['id' => 91, 'na' => 'الشمس', 'count' => 15],
        ['id' => 92, 'na' => 'الليل', 'count' => 21],
        ['id' => 93, 'na' => 'الضحى', 'count' => 11],
        ['id' => 94, 'na' => 'الشرح', 'count' => 8],
        ['id' => 95, 'na' => 'التين', 'count' => 8],
        ['id' => 96, 'na' => 'العلق', 'count' => 19],
        ['id' => 97, 'na' => 'القدر', 'count' => 5],
        ['id' => 98, 'na' => 'البينة', 'count' => 8],
        ['id' => 99, 'na' => 'الزلزلة', 'count' => 8],
        ['id' => 100, 'na' => 'العاديات', 'count' => 11],
        ['id' => 101, 'na' => 'القارعة', 'count' => 11],
        ['id' => 102, 'na' => 'التكاثر', 'count' => 8],
        ['id' => 103, 'na' => 'العصر', 'count' => 3],
        ['id' => 104, 'na' => 'الهمزة', 'count' => 9],
        ['id' => 105, 'na' => 'الفيل', 'count' => 5],
        ['id' => 106, 'na' => 'قريش', 'count' => 4],
        ['id' => 107, 'na' => 'الماعون', 'count' => 7],
        ['id' => 108, 'na' => 'الكوثر', 'count' => 3],
        ['id' => 109, 'na' => 'الكافرون', 'count' => 6],
        ['id' => 110, 'na' => 'النصر', 'count' => 3],
        ['id' => 111, 'na' => 'المسد', 'count' => 5],
        ['id' => 112, 'na' => 'الإخلاص', 'count' => 4],
        ['id' => 113, 'na' => 'الفلق', 'count' => 5],
        ['id' => 114, 'na' => 'الناس', 'count' => 6],    ],

    'total_verses' => 6236,

    'total_pages' => 604,

    'token_length' => 7,

    'dedication_types' => [
        'sadaqa' => 'صدقة عن',
        'gift' => 'هدية إلى',
    ],
];
