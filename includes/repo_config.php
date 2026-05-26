<?php

// Default git repository settings for this ITFlow fork.
define('ITFLOW_DEFAULT_REPO_URL', 'https://github.com/yutakabareru/itflow.git');
define('ITFLOW_DEFAULT_REPO_BRANCH', 'Custom');

function validateRepoUrl($repo_url) {
    return (bool) preg_match('/^https:\/\/github\.com\/[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+(\.git)?\/?$/', $repo_url);
}

function validateRepoBranch($repo_branch) {
    return (bool) preg_match('/^[A-Za-z0-9._\/-]+$/', $repo_branch);
}

function normalizeRepoUrl($repo_url) {
    $repo_url = rtrim(trim($repo_url), '/');
    if (!str_ends_with($repo_url, '.git')) {
        $repo_url .= '.git';
    }
    return $repo_url;
}
