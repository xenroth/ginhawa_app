<?php

return [
    'version' => is_file(base_path('VERSION')) ? trim(file_get_contents(base_path('VERSION'))) : env('GINHAWA_VERSION', '0.1.0'),
    'github_repository' => env('GINHAWA_GITHUB_REPOSITORY', 'xenroth/ginhawa_app'),
    'github_token' => env('GINHAWA_GITHUB_TOKEN'),
    'xrp_address' => env('GINHAWA_XRP_ADDRESS', ''),
    'xrp_memo' => env('GINHAWA_XRP_MEMO', ''),
];