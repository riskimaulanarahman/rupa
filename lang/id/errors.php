<?php

return [
    'detail_label' => 'Detail pesan',
    'details' => [
        'csrf_token_mismatch' => 'Sesi keamanan formulir sudah berakhir. Muat ulang halaman lalu coba kirim lagi.',
        'page_expired' => 'Halaman ini sudah kedaluwarsa. Silakan muat ulang dan coba kembali.',
        'method_not_allowed' => 'Aksi tersebut tidak didukung untuk halaman atau tautan ini.',
        'too_many_requests' => 'Permintaan terlalu sering. Mohon tunggu sebentar lalu coba lagi.',
        'unauthenticated' => 'Sesi login Anda sudah berakhir. Silakan login kembali.',
        'unauthorized' => 'Anda tidak memiliki izin untuk melakukan aksi ini.',
        'not_found' => 'Data atau halaman yang diminta tidak ditemukan.',
        'generic' => 'Terjadi kendala saat memproses permintaan Anda. Silakan coba lagi.',
    ],
    'actions' => [
        'go_home' => 'Kembali ke Beranda',
        'go_dashboard' => 'Kembali ke Dashboard',
        'go_appointments' => 'Ke Jadwal Appointment',
        'go_portal' => 'Kembali ke Portal Saya',
        'back' => 'Kembali',
    ],
    'status' => [
        '403' => [
            'title' => 'Akses Ditolak',
            'description' => 'Anda tidak memiliki izin untuk membuka halaman ini.',
        ],
        '404' => [
            'title' => 'Halaman Tidak Ditemukan',
            'description' => 'Halaman yang Anda cari mungkin sudah dipindahkan atau tidak tersedia.',
        ],
        '419' => [
            'title' => 'Halaman Kedaluwarsa',
            'description' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman dan coba lagi.',
        ],
        '429' => [
            'title' => 'Terlalu Banyak Permintaan',
            'description' => 'Permintaan Anda terlalu sering. Mohon tunggu sebentar lalu coba kembali.',
        ],
        '500' => [
            'title' => 'Terjadi Gangguan Server',
            'description' => 'Kami sedang memperbaiki masalah ini. Silakan coba beberapa saat lagi.',
        ],
        '503' => [
            'title' => 'Layanan Sementara Tidak Tersedia',
            'description' => 'Sistem sedang dalam pemeliharaan atau sibuk. Silakan coba kembali nanti.',
        ],
        '4xx' => [
            'title' => 'Permintaan Tidak Dapat Diproses',
            'description' => 'Terjadi masalah pada permintaan Anda. Silakan periksa kembali dan coba lagi.',
        ],
        '5xx' => [
            'title' => 'Terjadi Kesalahan Server',
            'description' => 'Terjadi gangguan di sisi server. Silakan coba lagi beberapa saat lagi.',
        ],
    ],
];
