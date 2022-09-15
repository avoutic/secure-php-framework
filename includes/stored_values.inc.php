<?php
namespace WebFramework\Core;

/*
CREATE TABLE IF NOT EXISTS config_values (
     module VARCHAR(255) NOT NULL,
     name VARCHAR(255) NOT NULL,
     value VARCHAR(255) NOT NULL,
     UNIQUE KEY `mod_name` (module,name)
);
*/

// Not a FrameworkCore object because it needs to operate before all components have been loaded.
// Therefore there is no StoreValue datacore object either.
//
class StoredValues
{
    private Database $database;
    private string $module;

    function __construct(string $module)
    {
        $this->database = WF::get_main_db();
        $this->module = $module;
    }

    /**
     * @return array<string>
     */
    public function get_values(): array
    {
        $result = $this->database->query('SELECT name, value FROM config_values WHERE module = ?',
            array($this->module));

        WF::verify($result !== false, "Failed to retrieve stored values for {$this->module}");

        $info = array();

        foreach ($result as $row)
            $info[$row['name']] = $row['value'];

        return $info;
    }

    public function get_value(string $name, string $default = ''): string
    {
        $result = $this->database->query('SELECT value FROM config_values WHERE module = ? AND name = ?',
            array($this->module, $name));

        WF::verify($result !== false, "Failed to retrieve stored value {$this->module}:{$name}");

        if ($result->RecordCount() == 0)
            return $default;

        if ($result->RecordCount() != 1)
            return '';

        return $result->fields['value'];
    }

    public function set_value(string $name, string $value): void
    {
        $result = $this->database->query('INSERT INTO config_values SET module = ?, name = ?, value = ? ON DUPLICATE KEY UPDATE value = ?',
            array($this->module, $name, $value, $value));

        WF::verify($result !== false, "Failed to set stored value {$this->module}:{$name}");
    }

    public function delete_value(string $name): void
    {
        $result = $this->database->query('DELETE config_values WHERE module = ? AND name = ?',
            array($this->module, $name));

        WF::verify($result !== false, "Failed to delete stored value {$this->module}:{$name}");
    }
};

/*
CREATE TABLE IF NOT EXISTS stored_user_values (
     user_id INT NOT NULL REFERENCES users(id),
     module VARCHAR(255) NOT NULL,
     name VARCHAR(255) NOT NULL,
     value VARCHAR(255) NOT NULL,
     UNIQUE KEY `user_mod_name` (user_id,module,name)
);
*/
class StoredUserValues
{
    private Database $database;
    private int $user_id;
    private string $module;

    function __construct(int $user_id, string $module)
    {
        $this->database = WF::get_main_db();
        $this->user_id = $user_id;
        $this->module = $module;
    }

    /**
     * @return array<string>
     */
    public function get_values(): array
    {
        $result = $this->database->query('SELECT name, value FROM user_config_values WHERE user_id = ? AND module = ?',
            array($this->user_id, $this->module));

        WF::verify($result !== false, "Failed to retrieve stored user values for {$this->user_id}:{$this->module}");

        $info = array();

        foreach ($result as $row)
            $info[$row['name']] = $row['value'];

        return $info;
    }

    public function value_exists(string $name): bool
    {
        $result = $this->database->query('SELECT value FROM user_config_values WHERE user_id = ? AND module = ? AND name = ?',
            array($this->user_id, $this->module, $name));

        WF::verify($result !== false, "Failed to retrieve stored user value {$this->user_id}:{$this->module}:{$name}");

        if ($result->RecordCount() == 0)
            return false;

        return true;
    }

    public function get_value(string $name, string $default = ''): string
    {
        $result = $this->database->query('SELECT value FROM user_config_values WHERE user_id = ? AND module = ? AND name = ?',
            array($this->user_id, $this->module, $name));

        WF::verify($result !== false, "Failed to retrieve stored user value {$this->user_id}:{$this->module}:{$name}");

        if ($result->RecordCount() == 0)
            return $default;

        if ($result->RecordCount() != 1)
            return '';

        return $result->fields['value'];
    }

    public function set_value(string $name, string $value): void
    {
        $result = $this->database->query('INSERT user_config_values SET user_id = ?, module = ?, name = ?, value = ? ON DUPLICATE KEY UPDATE value = ?',
            array($this->user_id, $this->module, $name, $value, $value));

        WF::verify($result !== false, "Failed to set stored user value {$this->user_id}:{$this->module}:{$name}");
    }

    public function delete_value(string $name): void
    {
        $result = $this->database->query('DELETE user_config_values WHERE user_id = ? AND module = ? AND name = ?',
            array($this->user_id, $this->module, $name));

        WF::verify($result !== false, "Failed to delete stored user value {$this->user_id}:{$this->module}:{$name}");
    }
};
?>