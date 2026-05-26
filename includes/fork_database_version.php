<?php
/*
 * Fork-specific database version tracking.
 * Keep fork migrations separate from upstream ITFlow database versions so
 * merging upstream releases does not skip or collide with custom schema changes.
 */

DEFINE("LATEST_FORK_DATABASE_VERSION", 1);
