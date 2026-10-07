<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Received = 'received';
    case Extracted = 'extracted'; // kept for backward compatibility with existing docs; new flow skips to Validated/NeedsReview
    case Validated = 'validated';
    case NeedsReview = 'needs_review';
    case Approved = 'approved';
}