CREATE TABLE IF NOT EXISTS xss_dom_captures (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
    cookie_name VARCHAR(80) NOT NULL,
    cookie_value VARCHAR(255) NOT NULL,
    source_hash TEXT NULL,
    page_url TEXT NULL,
    ip_address VARCHAR(64) NULL,
    user_agent TEXT NULL,
    captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_xss_dom_captures_date
    ON xss_dom_captures(captured_at DESC);
