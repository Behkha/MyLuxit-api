<?php

return array(
    'dsn' => env('SENTRY_LARAVEL_DSN', 'https://2394cce6b1694d2a8b9f1db986005b7b@sentry.io/1430351'),

    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,

    // Capture default user context
    'user_context' => false,
);
