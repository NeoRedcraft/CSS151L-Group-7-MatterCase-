-- Drop the database if it exists
DROP DATABASE IF EXISTS mattercase;

-- Create the database
CREATE DATABASE mattercase;
USE mattercase;

-- Set SQL mode and transaction settings
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- usertypes
-- 0 admin
-- 1 partner
-- 2 lawyer
-- 3 paralegal
-- 4 messenger

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usertype` int(1) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(150) NOT NULL,
  `pass` varchar(150) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- test admin
-- email: admin@email.com
-- password: password
INSERT INTO users (id, usertype,email, pass)
VALUES (1, 0, 'RDFAhvI7KF2y4RH6OPZJZGJLY0pxS2JvNFRPZS82THB3WUYwWVE9PQ==', '+lFCT9HtHdx4AwBuhiWSNkhqbWRJRUFCTVgvcHlIQjFKek9BZFE9PQ==');

-- Create Audit Log Table
CREATE TABLE `audit_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `action` TEXT NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE public.Clients (
    client_id bigint primary key generated always as identity,
    name text NOT NULL,
    contact_info text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE public.Cases (
    case_id bigint primary key generated always as identity,
    client_id bigint NOT NULL,
    lawyer_id bigint NOT NULL,
    case_field_name text NOT NULL,
    adversary_name text NOT NULL,
    case_type text NOT NULL CHECK (case_type IN ('Criminal', 'Civil', 'Family', 'Corporate', 'Other')),
    status text NOT NULL CHECK (status IN ('Open', 'Closed', 'Pending')),
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES public.Clients(client_id),
    FOREIGN KEY (lawyer_id) REFERENCES public.Users(user_id)
);

CREATE TABLE public.AuditLog (
    log_id bigint primary key generated always as identity,
    action text NOT NULL,
    user_id bigint NOT NULL,
    timestamp timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES public.Users(user_id)
);


CREATE TABLE public.Matter (
    client_id bigint primary key generated always as identity,
    name text NOT NULL,
    phone_number text,
    email text,
    user_id uuid NOT NULL,  -- Ensure this matches the data type in auth.users
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES auth.users(id)  -- Correctly references the id column in auth.users
);


CREATE INDEX idx_matter_user_id ON public.Matter(user_id);


CREATE TABLE public.CaseBilling (
    billing_id bigint primary key generated always as identity,
    matter_id bigint NOT NULL,  -- Reference to the Matter table
    billing_date timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    amount numeric(10, 2) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matter_id) REFERENCES public.Matter(client_id)  -- Connects to the Matter table
);


CREATE INDEX idx_case_billing_matter_id ON public.CaseBilling(matter_id);


CREATE TABLE public.EvidenceList (
    evidence_id bigint primary key generated always as identity,
    matter_id bigint NOT NULL,  -- Reference to the Matter table
    evidence_description text NOT NULL,
    evidence_date timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matter_id) REFERENCES public.Matter(client_id)  -- Connects to the Matter table
);


CREATE INDEX idx_evidence_list_matter_id ON public.EvidenceList(matter_id);


CREATE TABLE public.CasePage (
    page_id bigint primary key generated always as identity,
    matter_id bigint NOT NULL,  -- Reference to the Matter table
    page_content text NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matter_id) REFERENCES public.Matter(client_id)  -- Connects to the Matter table
);


CREATE INDEX idx_case_page_matter_id ON public.CasePage(matter_id);

CREATE TABLE public.FormList (
    form_id bigint primary key generated always as identity,
    matter_id bigint NOT NULL,  -- Reference to the Matter table
    form_name text NOT NULL,
    form_data jsonb,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matter_id) REFERENCES public.Matter(client_id)  -- Connects to the Matter table
);


CREATE INDEX idx_form_list_matter_id ON public.FormList(matter_id);

CREATE TABLE public.CaseFeesList (
    fee_id bigint primary key generated always as identity,
    matter_id bigint NOT NULL,  -- Reference to the Matter table
    fee_description text NOT NULL,
    fee_amount numeric(10, 2) NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matter_id) REFERENCES public.Matter(client_id)  -- Connects to the Matter table
);

CREATE INDEX idx_case_fees_list_matter_id ON public.CaseFeesList(matter_id);

