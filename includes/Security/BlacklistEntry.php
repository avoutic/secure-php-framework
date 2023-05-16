<?php

namespace WebFramework\Core\Security;

use WebFramework\Core\DataCore;

class BlacklistEntry extends DataCore
{
    protected static string $table_name = 'blacklist_entries';
    protected static array $base_fields = ['ip', 'user_id', 'severity', 'reason', 'timestamp'];
}