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
        ['id' => 1, 'na' => 'الفاتحة', 'en' => 'Al-Fatihah', 'count' => 7],
        ['id' => 2, 'na' => 'البقرة', 'en' => 'Al-Baqarah', 'count' => 286],
        ['id' => 3, 'na' => 'آل عمران', 'en' => 'Ali \'Imran', 'count' => 200],
        ['id' => 4, 'na' => 'النساء', 'en' => 'An-Nisa', 'count' => 176],
        ['id' => 5, 'na' => 'المائدة', 'en' => 'Al-Ma\'idah', 'count' => 120],
        ['id' => 6, 'na' => 'الأنعام', 'en' => 'Al-An\'am', 'count' => 165],
        ['id' => 7, 'na' => 'الأعراف', 'en' => 'Al-A\'raf', 'count' => 206],
        ['id' => 8, 'na' => 'الأنفال', 'en' => 'Al-Anfal', 'count' => 75],
        ['id' => 9, 'na' => 'التوبة', 'en' => 'At-Tawbah', 'count' => 129],
        ['id' => 10, 'na' => 'يونس', 'en' => 'Yunus', 'count' => 109],
        ['id' => 11, 'na' => 'هود', 'en' => 'Hud', 'count' => 123],
        ['id' => 12, 'na' => 'يوسف', 'en' => 'Yusuf', 'count' => 111],
        ['id' => 13, 'na' => 'الرعد', 'en' => 'Ar-Ra\'d', 'count' => 43],
        ['id' => 14, 'na' => 'ابراهيم', 'en' => 'Ibrahim', 'count' => 52],
        ['id' => 15, 'na' => 'الحجر', 'en' => 'Al-Hijr', 'count' => 99],
        ['id' => 16, 'na' => 'النحل', 'en' => 'An-Nahl', 'count' => 128],
        ['id' => 17, 'na' => 'الإسراء', 'en' => 'Al-Isra', 'count' => 111],
        ['id' => 18, 'na' => 'الكهف', 'en' => 'Al-Kahf', 'count' => 110],
        ['id' => 19, 'na' => 'مريم', 'en' => 'Maryam', 'count' => 98],
        ['id' => 20, 'na' => 'طه', 'en' => 'Taha', 'count' => 135],
        ['id' => 21, 'na' => 'الأنبياء', 'en' => 'Al-Anbya', 'count' => 112],
        ['id' => 22, 'na' => 'الحج', 'en' => 'Al-Hajj', 'count' => 78],
        ['id' => 23, 'na' => 'المؤمنون', 'en' => 'Al-Mu\'minun', 'count' => 118],
        ['id' => 24, 'na' => 'النور', 'en' => 'An-Nur', 'count' => 64],
        ['id' => 25, 'na' => 'الفرقان', 'en' => 'Al-Furqan', 'count' => 77],
        ['id' => 26, 'na' => 'الشعراء', 'en' => 'Ash-Shu\'ara', 'count' => 227],
        ['id' => 27, 'na' => 'النمل', 'en' => 'An-Naml', 'count' => 93],
        ['id' => 28, 'na' => 'القصص', 'en' => 'Al-Qasas', 'count' => 88],
        ['id' => 29, 'na' => 'العنكبوت', 'en' => 'Al-\'Ankabut', 'count' => 69],
        ['id' => 30, 'na' => 'الروم', 'en' => 'Ar-Rum', 'count' => 60],
        ['id' => 31, 'na' => 'لقمان', 'en' => 'Luqman', 'count' => 34],
        ['id' => 32, 'na' => 'السجدة', 'en' => 'As-Sajdah', 'count' => 30],
        ['id' => 33, 'na' => 'الأحزاب', 'en' => 'Al-Ahzab', 'count' => 73],
        ['id' => 34, 'na' => 'سبإ', 'en' => 'Saba', 'count' => 54],
        ['id' => 35, 'na' => 'فاطر', 'en' => 'Fatir', 'count' => 45],
        ['id' => 36, 'na' => 'يس', 'en' => 'Ya-Sin', 'count' => 83],
        ['id' => 37, 'na' => 'الصافات', 'en' => 'As-Saffat', 'count' => 182],
        ['id' => 38, 'na' => 'ص', 'en' => 'Sad', 'count' => 88],
        ['id' => 39, 'na' => 'الزمر', 'en' => 'Az-Zumar', 'count' => 75],
        ['id' => 40, 'na' => 'غافر', 'en' => 'Ghafir', 'count' => 85],
        ['id' => 41, 'na' => 'فصلت', 'en' => 'Fussilat', 'count' => 54],
        ['id' => 42, 'na' => 'الشورى', 'en' => 'Ash-Shuraa', 'count' => 53],
        ['id' => 43, 'na' => 'الزخرف', 'en' => 'Az-Zukhruf', 'count' => 89],
        ['id' => 44, 'na' => 'الدخان', 'en' => 'Ad-Dukhan', 'count' => 59],
        ['id' => 45, 'na' => 'الجاثية', 'en' => 'Al-Jathiyah', 'count' => 37],
        ['id' => 46, 'na' => 'الأحقاف', 'en' => 'Al-Ahqaf', 'count' => 35],
        ['id' => 47, 'na' => 'محمد', 'en' => 'Muhammad', 'count' => 38],
        ['id' => 48, 'na' => 'الفتح', 'en' => 'Al-Fath', 'count' => 29],
        ['id' => 49, 'na' => 'الحجرات', 'en' => 'Al-Hujurat', 'count' => 18],
        ['id' => 50, 'na' => 'ق', 'en' => 'Qaf', 'count' => 45],
        ['id' => 51, 'na' => 'الذاريات', 'en' => 'Adh-Dhariyat', 'count' => 60],
        ['id' => 52, 'na' => 'الطور', 'en' => 'At-Tur', 'count' => 49],
        ['id' => 53, 'na' => 'النجم', 'en' => 'An-Najm', 'count' => 62],
        ['id' => 54, 'na' => 'القمر', 'en' => 'Al-Qamar', 'count' => 55],
        ['id' => 55, 'na' => 'الرحمن', 'en' => 'Ar-Rahman', 'count' => 78],
        ['id' => 56, 'na' => 'الواقعة', 'en' => 'Al-Waqi\'ah', 'count' => 96],
        ['id' => 57, 'na' => 'الحديد', 'en' => 'Al-Hadid', 'count' => 29],
        ['id' => 58, 'na' => 'المجادلة', 'en' => 'Al-Mujadila', 'count' => 22],
        ['id' => 59, 'na' => 'الحشر', 'en' => 'Al-Hashr', 'count' => 24],
        ['id' => 60, 'na' => 'الممتحنة', 'en' => 'Al-Mumtahanah', 'count' => 13],
        ['id' => 61, 'na' => 'الصف', 'en' => 'As-Saf', 'count' => 14],
        ['id' => 62, 'na' => 'الجمعة', 'en' => 'Al-Jumu\'ah', 'count' => 11],
        ['id' => 63, 'na' => 'المنافقون', 'en' => 'Al-Munafiqun', 'count' => 11],
        ['id' => 64, 'na' => 'التغابن', 'en' => 'At-Taghabun', 'count' => 18],
        ['id' => 65, 'na' => 'الطلاق', 'en' => 'At-Talaq', 'count' => 12],
        ['id' => 66, 'na' => 'التحريم', 'en' => 'At-Tahrim', 'count' => 12],
        ['id' => 67, 'na' => 'الملك', 'en' => 'Al-Mulk', 'count' => 30],
        ['id' => 68, 'na' => 'القلم', 'en' => 'Al-Qalam', 'count' => 52],
        ['id' => 69, 'na' => 'الحاقة', 'en' => 'Al-Haqqah', 'count' => 52],
        ['id' => 70, 'na' => 'المعارج', 'en' => 'Al-Ma\'arij', 'count' => 44],
        ['id' => 71, 'na' => 'نوح', 'en' => 'Nuh', 'count' => 28],
        ['id' => 72, 'na' => 'الجن', 'en' => 'Al-Jinn', 'count' => 28],
        ['id' => 73, 'na' => 'المزمل', 'en' => 'Al-Muzzammil', 'count' => 20],
        ['id' => 74, 'na' => 'المدثر', 'en' => 'Al-Muddaththir', 'count' => 56],
        ['id' => 75, 'na' => 'القيامة', 'en' => 'Al-Qiyamah', 'count' => 40],
        ['id' => 76, 'na' => 'الانسان', 'en' => 'Al-Insan', 'count' => 31],
        ['id' => 77, 'na' => 'المرسلات', 'en' => 'Al-Mursalat', 'count' => 50],
        ['id' => 78, 'na' => 'النبإ', 'en' => 'An-Naba', 'count' => 40],
        ['id' => 79, 'na' => 'النازعات', 'en' => 'An-Nazi\'at', 'count' => 46],
        ['id' => 80, 'na' => 'عبس', 'en' => '\'Abasa', 'count' => 42],
        ['id' => 81, 'na' => 'التكوير', 'en' => 'At-Takwir', 'count' => 29],
        ['id' => 82, 'na' => 'الإنفطار', 'en' => 'Al-Infitar', 'count' => 19],
        ['id' => 83, 'na' => 'المطففين', 'en' => 'Al-Mutaffifin', 'count' => 36],
        ['id' => 84, 'na' => 'الإنشقاق', 'en' => 'Al-Inshiqaq', 'count' => 25],
        ['id' => 85, 'na' => 'البروج', 'en' => 'Al-Buruj', 'count' => 22],
        ['id' => 86, 'na' => 'الطارق', 'en' => 'At-Tariq', 'count' => 17],
        ['id' => 87, 'na' => 'الأعلى', 'en' => 'Al-A\'la', 'count' => 19],
        ['id' => 88, 'na' => 'الغاشية', 'en' => 'Al-Ghashiyah', 'count' => 26],
        ['id' => 89, 'na' => 'الفجر', 'en' => 'Al-Fajr', 'count' => 30],
        ['id' => 90, 'na' => 'البلد', 'en' => 'Al-Balad', 'count' => 20],
        ['id' => 91, 'na' => 'الشمس', 'en' => 'Ash-Shams', 'count' => 15],
        ['id' => 92, 'na' => 'الليل', 'en' => 'Al-Layl', 'count' => 21],
        ['id' => 93, 'na' => 'الضحى', 'en' => 'Ad-Duhaa', 'count' => 11],
        ['id' => 94, 'na' => 'الشرح', 'en' => 'Ash-Sharh', 'count' => 8],
        ['id' => 95, 'na' => 'التين', 'en' => 'At-Tin', 'count' => 8],
        ['id' => 96, 'na' => 'العلق', 'en' => 'Al-\'Alaq', 'count' => 19],
        ['id' => 97, 'na' => 'القدر', 'en' => 'Al-Qadr', 'count' => 5],
        ['id' => 98, 'na' => 'البينة', 'en' => 'Al-Bayyinah', 'count' => 8],
        ['id' => 99, 'na' => 'الزلزلة', 'en' => 'Az-Zalzalah', 'count' => 8],
        ['id' => 100, 'na' => 'العاديات', 'en' => 'Al-\'Adiyat', 'count' => 11],
        ['id' => 101, 'na' => 'القارعة', 'en' => 'Al-Qari\'ah', 'count' => 11],
        ['id' => 102, 'na' => 'التكاثر', 'en' => 'At-Takathur', 'count' => 8],
        ['id' => 103, 'na' => 'العصر', 'en' => 'Al-\'Asr', 'count' => 3],
        ['id' => 104, 'na' => 'الهمزة', 'en' => 'Al-Humazah', 'count' => 9],
        ['id' => 105, 'na' => 'الفيل', 'en' => 'Al-Fil', 'count' => 5],
        ['id' => 106, 'na' => 'قريش', 'en' => 'Quraysh', 'count' => 4],
        ['id' => 107, 'na' => 'الماعون', 'en' => 'Al-Ma\'un', 'count' => 7],
        ['id' => 108, 'na' => 'الكوثر', 'en' => 'Al-Kawthar', 'count' => 3],
        ['id' => 109, 'na' => 'الكافرون', 'en' => 'Al-Kafirun', 'count' => 6],
        ['id' => 110, 'na' => 'النصر', 'en' => 'An-Nasr', 'count' => 3],
        ['id' => 111, 'na' => 'المسد', 'en' => 'Al-Masad', 'count' => 5],
        ['id' => 112, 'na' => 'الإخلاص', 'en' => 'Al-Ikhlas', 'count' => 4],
        ['id' => 113, 'na' => 'الفلق', 'en' => 'Al-Falaq', 'count' => 5],
        ['id' => 114, 'na' => 'الناس', 'en' => 'An-Nas', 'count' => 6],
    ],

    'total_verses' => 6236,

    'total_pages' => 604,

    'token_length' => 7,

    'dedication_types' => [
        'sadaqa' => 'صدقة عن',
        'gift' => 'هدية إلى',
    ],
];
