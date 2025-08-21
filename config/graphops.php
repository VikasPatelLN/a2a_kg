
<?php
return [
    // Gate/ability name used to protect the GraphOps admin endpoints
    'ability' => env('GRAPHOPS_ABILITY', 'graphops-rebuild'),

    // Comma-separated user IDs that are allowed to access GraphOps UI if you
    // don't have a dedicated role/ability wired in your app yet.
    // Example: GRAPHOPS_USER_IDS="1,2,15"
    'authorized_user_ids' => env('GRAPHOPS_USER_IDS', ''),
];
