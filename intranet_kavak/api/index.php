<?php
// Vercel Serverless Function entrypoint
// Changes directory to the project root to maintain local path structures
chdir(__DIR__ . '/../');
require_once __DIR__ . '/../index.php';
