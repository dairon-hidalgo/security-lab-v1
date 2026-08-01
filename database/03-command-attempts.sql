CREATE TABLE IF NOT EXISTS command_attempts (
    id BIGSERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    input_value TEXT NOT NULL,
    executed_command TEXT NOT NULL,
    command_output TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_command_attempts_date
    ON command_attempts(executed_at DESC);

CREATE INDEX IF NOT EXISTS idx_command_attempts_user
    ON command_attempts(user_id);