CREATE TABLE IF NOT EXISTS xss_reflected_attempts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    payload TEXT NOT NULL,
    ip_address VARCHAR(64),
    user_agent TEXT,
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
