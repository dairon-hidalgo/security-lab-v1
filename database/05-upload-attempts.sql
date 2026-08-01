CREATE TABLE IF NOT EXISTS upload_attempts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    reported_mime VARCHAR(150),
    file_size BIGINT NOT NULL DEFAULT 0,
    was_successful BOOLEAN NOT NULL DEFAULT FALSE,
    public_url VARCHAR(500),
    ip_address VARCHAR(64),
    user_agent TEXT,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
