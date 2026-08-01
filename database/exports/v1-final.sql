--
-- PostgreSQL database dump
--

\restrict 8eEoQLPma07dnUtTodCAloi0JjXhldOV3kE5AcHuwjAd2DsAfrJCj71wY2bZQHG

-- Dumped from database version 16.14 (Debian 16.14-1.pgdg13+1)
-- Dumped by pg_dump version 16.14 (Debian 16.14-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.xss_stored_comments DROP CONSTRAINT IF EXISTS xss_stored_comments_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.xss_reflected_attempts DROP CONSTRAINT IF EXISTS xss_reflected_attempts_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.xss_dom_captures DROP CONSTRAINT IF EXISTS xss_dom_captures_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.xss_cookie_captures DROP CONSTRAINT IF EXISTS xss_cookie_captures_captured_by_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.upload_attempts DROP CONSTRAINT IF EXISTS upload_attempts_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.tickets DROP CONSTRAINT IF EXISTS tickets_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.file_include_attempts DROP CONSTRAINT IF EXISTS file_include_attempts_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.command_attempts DROP CONSTRAINT IF EXISTS command_attempts_user_id_fkey;
DROP INDEX IF EXISTS public.idx_xss_dom_captures_date;
DROP INDEX IF EXISTS public.idx_login_attempts_username;
DROP INDEX IF EXISTS public.idx_login_attempts_date;
DROP INDEX IF EXISTS public.idx_file_include_attempts_user;
DROP INDEX IF EXISTS public.idx_file_include_attempts_date;
DROP INDEX IF EXISTS public.idx_command_attempts_user;
DROP INDEX IF EXISTS public.idx_command_attempts_date;
ALTER TABLE IF EXISTS ONLY public.xss_stored_comments DROP CONSTRAINT IF EXISTS xss_stored_comments_pkey;
ALTER TABLE IF EXISTS ONLY public.xss_reflected_attempts DROP CONSTRAINT IF EXISTS xss_reflected_attempts_pkey;
ALTER TABLE IF EXISTS ONLY public.xss_dom_captures DROP CONSTRAINT IF EXISTS xss_dom_captures_pkey;
ALTER TABLE IF EXISTS ONLY public.xss_cookie_captures DROP CONSTRAINT IF EXISTS xss_cookie_captures_pkey;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_username_key;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY public.upload_attempts DROP CONSTRAINT IF EXISTS upload_attempts_pkey;
ALTER TABLE IF EXISTS ONLY public.tickets DROP CONSTRAINT IF EXISTS tickets_pkey;
ALTER TABLE IF EXISTS ONLY public.sqli_audit DROP CONSTRAINT IF EXISTS sqli_audit_pkey;
ALTER TABLE IF EXISTS ONLY public.login_attempts DROP CONSTRAINT IF EXISTS login_attempts_pkey;
ALTER TABLE IF EXISTS ONLY public.file_include_attempts DROP CONSTRAINT IF EXISTS file_include_attempts_pkey;
ALTER TABLE IF EXISTS ONLY public.command_attempts DROP CONSTRAINT IF EXISTS command_attempts_pkey;
ALTER TABLE IF EXISTS public.xss_stored_comments ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.xss_reflected_attempts ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.xss_dom_captures ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.xss_cookie_captures ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.users ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.upload_attempts ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.tickets ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.sqli_audit ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.login_attempts ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.file_include_attempts ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.command_attempts ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.xss_stored_comments_id_seq;
DROP TABLE IF EXISTS public.xss_stored_comments;
DROP SEQUENCE IF EXISTS public.xss_reflected_attempts_id_seq;
DROP TABLE IF EXISTS public.xss_reflected_attempts;
DROP SEQUENCE IF EXISTS public.xss_dom_captures_id_seq;
DROP TABLE IF EXISTS public.xss_dom_captures;
DROP SEQUENCE IF EXISTS public.xss_cookie_captures_id_seq;
DROP TABLE IF EXISTS public.xss_cookie_captures;
DROP SEQUENCE IF EXISTS public.users_id_seq;
DROP TABLE IF EXISTS public.users;
DROP SEQUENCE IF EXISTS public.upload_attempts_id_seq;
DROP TABLE IF EXISTS public.upload_attempts;
DROP SEQUENCE IF EXISTS public.tickets_id_seq;
DROP TABLE IF EXISTS public.tickets;
DROP SEQUENCE IF EXISTS public.sqli_audit_id_seq;
DROP TABLE IF EXISTS public.sqli_audit;
DROP SEQUENCE IF EXISTS public.login_attempts_id_seq;
DROP TABLE IF EXISTS public.login_attempts;
DROP SEQUENCE IF EXISTS public.file_include_attempts_id_seq;
DROP TABLE IF EXISTS public.file_include_attempts;
DROP SEQUENCE IF EXISTS public.command_attempts_id_seq;
DROP TABLE IF EXISTS public.command_attempts;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: command_attempts; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.command_attempts (
    id bigint NOT NULL,
    user_id integer,
    input_value text NOT NULL,
    executed_command text NOT NULL,
    command_output text,
    ip_address character varying(45),
    user_agent text,
    executed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.command_attempts OWNER TO labuser;

--
-- Name: command_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.command_attempts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.command_attempts_id_seq OWNER TO labuser;

--
-- Name: command_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.command_attempts_id_seq OWNED BY public.command_attempts.id;


--
-- Name: file_include_attempts; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.file_include_attempts (
    id bigint NOT NULL,
    user_id integer,
    resource_value text NOT NULL,
    resource_type character varying(20) NOT NULL,
    was_successful boolean DEFAULT false NOT NULL,
    result_excerpt text,
    ip_address character varying(45),
    user_agent text,
    attempted_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.file_include_attempts OWNER TO labuser;

--
-- Name: file_include_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.file_include_attempts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.file_include_attempts_id_seq OWNER TO labuser;

--
-- Name: file_include_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.file_include_attempts_id_seq OWNED BY public.file_include_attempts.id;


--
-- Name: login_attempts; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.login_attempts (
    id bigint NOT NULL,
    username character varying(100) NOT NULL,
    ip_address character varying(45) NOT NULL,
    user_agent text,
    was_successful boolean DEFAULT false NOT NULL,
    attempted_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.login_attempts OWNER TO labuser;

--
-- Name: login_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.login_attempts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.login_attempts_id_seq OWNER TO labuser;

--
-- Name: login_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.login_attempts_id_seq OWNED BY public.login_attempts.id;


--
-- Name: sqli_audit; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.sqli_audit (
    id bigint NOT NULL,
    supplied_id text NOT NULL,
    executed_query text NOT NULL,
    result_count integer DEFAULT 0 NOT NULL,
    error_message text,
    client_ip character varying(64),
    user_agent text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.sqli_audit OWNER TO labuser;

--
-- Name: sqli_audit_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.sqli_audit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sqli_audit_id_seq OWNER TO labuser;

--
-- Name: sqli_audit_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.sqli_audit_id_seq OWNED BY public.sqli_audit.id;


--
-- Name: tickets; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.tickets (
    id integer NOT NULL,
    user_id integer,
    title character varying(150) NOT NULL,
    description text NOT NULL,
    status character varying(30) DEFAULT 'abierto'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.tickets OWNER TO labuser;

--
-- Name: tickets_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.tickets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tickets_id_seq OWNER TO labuser;

--
-- Name: tickets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.tickets_id_seq OWNED BY public.tickets.id;


--
-- Name: upload_attempts; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.upload_attempts (
    id integer NOT NULL,
    user_id integer,
    original_name character varying(255) NOT NULL,
    stored_name character varying(255) NOT NULL,
    reported_mime character varying(150),
    file_size bigint DEFAULT 0 NOT NULL,
    was_successful boolean DEFAULT false NOT NULL,
    public_url character varying(500),
    ip_address character varying(64),
    user_agent text,
    uploaded_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.upload_attempts OWNER TO labuser;

--
-- Name: upload_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.upload_attempts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.upload_attempts_id_seq OWNER TO labuser;

--
-- Name: upload_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.upload_attempts_id_seq OWNED BY public.upload_attempts.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    password character varying(255) NOT NULL,
    full_name character varying(100) NOT NULL,
    role character varying(20) DEFAULT 'user'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.users OWNER TO labuser;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO labuser;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: xss_cookie_captures; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.xss_cookie_captures (
    id integer NOT NULL,
    captured_by_user_id integer,
    cookie_name character varying(100) NOT NULL,
    cookie_value text NOT NULL,
    page_url text,
    ip_address character varying(64),
    captured_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.xss_cookie_captures OWNER TO labuser;

--
-- Name: xss_cookie_captures_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.xss_cookie_captures_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.xss_cookie_captures_id_seq OWNER TO labuser;

--
-- Name: xss_cookie_captures_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.xss_cookie_captures_id_seq OWNED BY public.xss_cookie_captures.id;


--
-- Name: xss_dom_captures; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.xss_dom_captures (
    id integer NOT NULL,
    user_id integer,
    cookie_name character varying(80) NOT NULL,
    cookie_value character varying(255) NOT NULL,
    source_hash text,
    page_url text,
    ip_address character varying(64),
    user_agent text,
    captured_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.xss_dom_captures OWNER TO labuser;

--
-- Name: xss_dom_captures_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.xss_dom_captures_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.xss_dom_captures_id_seq OWNER TO labuser;

--
-- Name: xss_dom_captures_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.xss_dom_captures_id_seq OWNED BY public.xss_dom_captures.id;


--
-- Name: xss_reflected_attempts; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.xss_reflected_attempts (
    id integer NOT NULL,
    user_id integer,
    payload text NOT NULL,
    ip_address character varying(64),
    user_agent text,
    requested_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.xss_reflected_attempts OWNER TO labuser;

--
-- Name: xss_reflected_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.xss_reflected_attempts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.xss_reflected_attempts_id_seq OWNER TO labuser;

--
-- Name: xss_reflected_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.xss_reflected_attempts_id_seq OWNED BY public.xss_reflected_attempts.id;


--
-- Name: xss_stored_comments; Type: TABLE; Schema: public; Owner: labuser
--

CREATE TABLE public.xss_stored_comments (
    id integer NOT NULL,
    user_id integer,
    author_name character varying(100) NOT NULL,
    content text NOT NULL,
    ip_address character varying(64),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.xss_stored_comments OWNER TO labuser;

--
-- Name: xss_stored_comments_id_seq; Type: SEQUENCE; Schema: public; Owner: labuser
--

CREATE SEQUENCE public.xss_stored_comments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.xss_stored_comments_id_seq OWNER TO labuser;

--
-- Name: xss_stored_comments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labuser
--

ALTER SEQUENCE public.xss_stored_comments_id_seq OWNED BY public.xss_stored_comments.id;


--
-- Name: command_attempts id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.command_attempts ALTER COLUMN id SET DEFAULT nextval('public.command_attempts_id_seq'::regclass);


--
-- Name: file_include_attempts id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.file_include_attempts ALTER COLUMN id SET DEFAULT nextval('public.file_include_attempts_id_seq'::regclass);


--
-- Name: login_attempts id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.login_attempts ALTER COLUMN id SET DEFAULT nextval('public.login_attempts_id_seq'::regclass);


--
-- Name: sqli_audit id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.sqli_audit ALTER COLUMN id SET DEFAULT nextval('public.sqli_audit_id_seq'::regclass);


--
-- Name: tickets id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.tickets ALTER COLUMN id SET DEFAULT nextval('public.tickets_id_seq'::regclass);


--
-- Name: upload_attempts id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.upload_attempts ALTER COLUMN id SET DEFAULT nextval('public.upload_attempts_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: xss_cookie_captures id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_cookie_captures ALTER COLUMN id SET DEFAULT nextval('public.xss_cookie_captures_id_seq'::regclass);


--
-- Name: xss_dom_captures id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_dom_captures ALTER COLUMN id SET DEFAULT nextval('public.xss_dom_captures_id_seq'::regclass);


--
-- Name: xss_reflected_attempts id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_reflected_attempts ALTER COLUMN id SET DEFAULT nextval('public.xss_reflected_attempts_id_seq'::regclass);


--
-- Name: xss_stored_comments id; Type: DEFAULT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_stored_comments ALTER COLUMN id SET DEFAULT nextval('public.xss_stored_comments_id_seq'::regclass);


--
-- Data for Name: command_attempts; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.command_attempts (id, user_id, input_value, executed_command, command_output, ip_address, user_agent, executed_at) FROM stdin;
\.


--
-- Data for Name: file_include_attempts; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.file_include_attempts (id, user_id, resource_value, resource_type, was_successful, result_excerpt, ip_address, user_agent, attempted_at) FROM stdin;
\.


--
-- Data for Name: login_attempts; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.login_attempts (id, username, ip_address, user_agent, was_successful, attempted_at) FROM stdin;
1	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	t	2026-08-01 04:43:43.420448
2	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	t	2026-08-01 04:49:28.747257
3	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	t	2026-08-01 04:56:21.982944
4	admin	172.19.0.1	Mozilla/5.0 (Windows NT; Windows NT 10.0; es-PE) WindowsPowerShell/5.1.26100.8894	t	2026-08-01 06:07:54.811369
5	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	t	2026-08-01 06:08:08.634337
6	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:38:51.372192
7	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:38:53.280319
8	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	t	2026-08-01 06:38:53.631721
9	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:02.39412
10	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:04.909791
11	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:07.254548
12	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:13.272533
13	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:15.680869
14	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:17.780295
15	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:19.748618
16	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:21.759129
17	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:25.075824
18	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:25.628576
19	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:27.075222
20	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:28.805735
21	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:32.072364
22	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:35.097881
23	admin '1 = 1'	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	f	2026-08-01 06:39:53.12146
24	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	t	2026-08-01 16:26:02.504446
25	admin	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	t	2026-08-01 16:32:16.809592
\.


--
-- Data for Name: sqli_audit; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.sqli_audit (id, supplied_id, executed_query, result_count, error_message, client_ip, user_agent, created_at) FROM stdin;
1	1	\n        SELECT\n            id,\n            username,\n            full_name,\n            role\n        FROM users\n        WHERE id = 1\n        ORDER BY id\n    	1	\N	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	2026-08-01 05:03:47.858047
2	1	\n        SELECT\n            id,\n            username,\n            full_name,\n            role\n        FROM users\n        WHERE id = 1\n        ORDER BY id\n    	1	\N	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	2026-08-01 05:04:19.728056
3	1	\n        SELECT\n            id,\n            username,\n            full_name,\n            role\n        FROM users\n        WHERE id = 1\n        ORDER BY id\n    	1	\N	172.19.0.1	Mozilla/5.0 (Windows NT; Windows NT 10.0; es-PE) WindowsPowerShell/5.1.26100.8894	2026-08-01 05:04:34.755635
4	1 OR 1=1	\n        SELECT\n            id,\n            username,\n            full_name,\n            role\n        FROM users\n        WHERE id = 1 OR 1=1\n        ORDER BY id\n    	7	\N	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	2026-08-01 05:04:41.469896
5	1 OR 1=1	\n        SELECT\n            id,\n            username,\n            full_name,\n            role\n        FROM users\n        WHERE id = 1 OR 1=1\n        ORDER BY id\n    	7	\N	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	2026-08-01 05:04:43.466097
6	1 OR 1=1	\n        SELECT\n            id,\n            username,\n            full_name,\n            role\n        FROM users\n        WHERE id = 1 OR 1=1\n        ORDER BY id\n    	7	\N	172.19.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 OPR/133.0.0.0	2026-08-01 05:05:13.297668
\.


--
-- Data for Name: tickets; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.tickets (id, user_id, title, description, status, created_at) FROM stdin;
1	3	Problema de acceso	No puedo ingresar al sistema académico.	abierto	2026-08-01 03:57:17.93983
2	2	Actualización de software	Se requiere actualizar una estación de trabajo.	en proceso	2026-08-01 03:57:17.941429
3	2	Actualizaci??n de software	Se requiere actualizar una estaci??n de trabajo.	en proceso	2026-08-01 06:07:15.421152
\.


--
-- Data for Name: upload_attempts; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.upload_attempts (id, user_id, original_name, stored_name, reported_mime, file_size, was_successful, public_url, ip_address, user_agent, uploaded_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.users (id, username, password, full_name, role, created_at) FROM stdin;
1	admin	admin123	Administrador FIIS	admin	2026-08-01 03:57:17.937914
2	analista	fiis2026	Analista de Soporte	analyst	2026-08-01 03:57:17.937914
3	usuario	password	Usuario de Prueba	user	2026-08-01 03:57:17.937914
4	soporte1	soporte123	Carlos Mendoza	support	2026-08-01 05:03:22.149348
6	docente	docente123	Docente de Prueba	teacher	2026-08-01 05:03:22.149348
7	estudiante	unas2026	Estudiante de Prueba	student	2026-08-01 05:03:22.149348
5	soporte2	clave2026	Lucía Fernández	support	2026-08-01 05:03:22.149348
15	mesa01	mesa123	Andrea Torres	support	2026-08-01 16:34:49.089503
16	mesa02	soporte2026	Miguel Salazar	support	2026-08-01 16:34:49.089503
17	mesa03	helpdesk01	Daniela Rojas	support	2026-08-01 16:34:49.089503
18	tecnico01	tecnico123	Jorge Ramirez	technician	2026-08-01 16:34:49.089503
19	tecnico02	redes2026	Paola Castro	technician	2026-08-01 16:34:49.089503
20	tecnico03	hardware01	Renato Mendoza	technician	2026-08-01 16:34:49.089503
21	docente01	docente123	Julio Paredes	teacher	2026-08-01 16:34:49.089503
22	docente02	clases2026	Mariana Vega	teacher	2026-08-01 16:34:49.089503
23	docente03	fiisdocente	Fernando Ruiz	teacher	2026-08-01 16:34:49.089503
24	alumno01	alumno123	Pedro Flores	student	2026-08-01 16:34:49.089503
25	alumno02	universidad	Valeria Soto	student	2026-08-01 16:34:49.089503
26	alumno03	sistemas2026	Diego Navarro	student	2026-08-01 16:34:49.089503
27	alumno04	password123	Camila Herrera	student	2026-08-01 16:34:49.089503
28	secretaria01	secretaria	Rosa Medina	staff	2026-08-01 16:34:49.089503
29	laboratorio	laboratorio	Encargado de Lab	staff	2026-08-01 16:34:49.089503
30	coordinador	coord123	Carlos Zamora	coordinator	2026-08-01 16:34:49.089503
31	auditor	auditoria2026	Auditor de Seguridad	auditor	2026-08-01 16:34:49.089503
32	invitado01	guest	Usuario Invitado Uno	guest	2026-08-01 16:34:49.089503
33	invitado02	guest123	Usuario Invitado Dos	guest	2026-08-01 16:34:49.089503
34	pruebas	test123	Cuenta de Pruebas	tester	2026-08-01 16:34:49.089503
\.


--
-- Data for Name: xss_cookie_captures; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.xss_cookie_captures (id, captured_by_user_id, cookie_name, cookie_value, page_url, ip_address, captured_at) FROM stdin;
\.


--
-- Data for Name: xss_dom_captures; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.xss_dom_captures (id, user_id, cookie_name, cookie_value, source_hash, page_url, ip_address, user_agent, captured_at) FROM stdin;
\.


--
-- Data for Name: xss_reflected_attempts; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.xss_reflected_attempts (id, user_id, payload, ip_address, user_agent, requested_at) FROM stdin;
\.


--
-- Data for Name: xss_stored_comments; Type: TABLE DATA; Schema: public; Owner: labuser
--

COPY public.xss_stored_comments (id, user_id, author_name, content, ip_address, created_at) FROM stdin;
\.


--
-- Name: command_attempts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.command_attempts_id_seq', 1, false);


--
-- Name: file_include_attempts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.file_include_attempts_id_seq', 1, false);


--
-- Name: login_attempts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.login_attempts_id_seq', 25, true);


--
-- Name: sqli_audit_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.sqli_audit_id_seq', 6, true);


--
-- Name: tickets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.tickets_id_seq', 3, true);


--
-- Name: upload_attempts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.upload_attempts_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.users_id_seq', 34, true);


--
-- Name: xss_cookie_captures_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.xss_cookie_captures_id_seq', 1, false);


--
-- Name: xss_dom_captures_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.xss_dom_captures_id_seq', 1, false);


--
-- Name: xss_reflected_attempts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.xss_reflected_attempts_id_seq', 1, false);


--
-- Name: xss_stored_comments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: labuser
--

SELECT pg_catalog.setval('public.xss_stored_comments_id_seq', 1, false);


--
-- Name: command_attempts command_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.command_attempts
    ADD CONSTRAINT command_attempts_pkey PRIMARY KEY (id);


--
-- Name: file_include_attempts file_include_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.file_include_attempts
    ADD CONSTRAINT file_include_attempts_pkey PRIMARY KEY (id);


--
-- Name: login_attempts login_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.login_attempts
    ADD CONSTRAINT login_attempts_pkey PRIMARY KEY (id);


--
-- Name: sqli_audit sqli_audit_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.sqli_audit
    ADD CONSTRAINT sqli_audit_pkey PRIMARY KEY (id);


--
-- Name: tickets tickets_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_pkey PRIMARY KEY (id);


--
-- Name: upload_attempts upload_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.upload_attempts
    ADD CONSTRAINT upload_attempts_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: xss_cookie_captures xss_cookie_captures_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_cookie_captures
    ADD CONSTRAINT xss_cookie_captures_pkey PRIMARY KEY (id);


--
-- Name: xss_dom_captures xss_dom_captures_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_dom_captures
    ADD CONSTRAINT xss_dom_captures_pkey PRIMARY KEY (id);


--
-- Name: xss_reflected_attempts xss_reflected_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_reflected_attempts
    ADD CONSTRAINT xss_reflected_attempts_pkey PRIMARY KEY (id);


--
-- Name: xss_stored_comments xss_stored_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_stored_comments
    ADD CONSTRAINT xss_stored_comments_pkey PRIMARY KEY (id);


--
-- Name: idx_command_attempts_date; Type: INDEX; Schema: public; Owner: labuser
--

CREATE INDEX idx_command_attempts_date ON public.command_attempts USING btree (executed_at DESC);


--
-- Name: idx_command_attempts_user; Type: INDEX; Schema: public; Owner: labuser
--

CREATE INDEX idx_command_attempts_user ON public.command_attempts USING btree (user_id);


--
-- Name: idx_file_include_attempts_date; Type: INDEX; Schema: public; Owner: labuser
--

CREATE INDEX idx_file_include_attempts_date ON public.file_include_attempts USING btree (attempted_at DESC);


--
-- Name: idx_file_include_attempts_user; Type: INDEX; Schema: public; Owner: labuser
--

CREATE INDEX idx_file_include_attempts_user ON public.file_include_attempts USING btree (user_id);


--
-- Name: idx_login_attempts_date; Type: INDEX; Schema: public; Owner: labuser
--

CREATE INDEX idx_login_attempts_date ON public.login_attempts USING btree (attempted_at DESC);


--
-- Name: idx_login_attempts_username; Type: INDEX; Schema: public; Owner: labuser
--

CREATE INDEX idx_login_attempts_username ON public.login_attempts USING btree (username);


--
-- Name: idx_xss_dom_captures_date; Type: INDEX; Schema: public; Owner: labuser
--

CREATE INDEX idx_xss_dom_captures_date ON public.xss_dom_captures USING btree (captured_at DESC);


--
-- Name: command_attempts command_attempts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.command_attempts
    ADD CONSTRAINT command_attempts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: file_include_attempts file_include_attempts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.file_include_attempts
    ADD CONSTRAINT file_include_attempts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: tickets tickets_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: upload_attempts upload_attempts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.upload_attempts
    ADD CONSTRAINT upload_attempts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: xss_cookie_captures xss_cookie_captures_captured_by_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_cookie_captures
    ADD CONSTRAINT xss_cookie_captures_captured_by_user_id_fkey FOREIGN KEY (captured_by_user_id) REFERENCES public.users(id);


--
-- Name: xss_dom_captures xss_dom_captures_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_dom_captures
    ADD CONSTRAINT xss_dom_captures_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: xss_reflected_attempts xss_reflected_attempts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_reflected_attempts
    ADD CONSTRAINT xss_reflected_attempts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: xss_stored_comments xss_stored_comments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labuser
--

ALTER TABLE ONLY public.xss_stored_comments
    ADD CONSTRAINT xss_stored_comments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- PostgreSQL database dump complete
--

\unrestrict 8eEoQLPma07dnUtTodCAloi0JjXhldOV3kE5AcHuwjAd2DsAfrJCj71wY2bZQHG

