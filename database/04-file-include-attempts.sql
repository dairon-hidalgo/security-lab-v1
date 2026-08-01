CREATE TABLE IF NOT EXISTS file_include_attempts (
    id BIGSERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    resource_value TEXT NOT NULL,
    resource_type VARCHAR(20) NOT NULL,
    was_successful BOOLEAN NOT NULL DEFAULT FALSE,
    result_excerpt TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_file_include_attempts_date
    ON file_include_attempts(attempted_at DESC);

CREATE INDEX IF NOT EXISTS idx_file_include_attempts_user
    ON file_include_attempts(user_id);