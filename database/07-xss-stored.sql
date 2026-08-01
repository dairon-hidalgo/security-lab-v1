CREATE TABLE IF NOT EXISTS xss_stored_comments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    author_name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    ip_address VARCHAR(64),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS xss_cookie_captures (
    id SERIAL PRIMARY KEY,
    captured_by_user_id INTEGER REFERENCES users(id),
    cookie_name VARCHAR(100) NOT NULL,
    cookie_value TEXT NOT NULL,
    page_url TEXT,
    ip_address VARCHAR(64),
    captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
