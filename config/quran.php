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

    'total_verses' => 6236,

    'total_pages' => 604,

    'token_length' => 7,

    'dedication_types' => [
        'sadaqa' => 'صدقة عن',
        'gift' => 'هدية إلى',
    ],
];
