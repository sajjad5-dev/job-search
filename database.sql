CREATE DATABASE IF NOT EXISTS jobsearch_db;
USE jobsearch_db;

CREATE TABLE users (
    id         int AUTO_INCREMENT PRIMARY KEY,
    name       nvarchar(100) not null,
    email      nvarchar(150) not null UNIQUE,
    password   nvarchar(255) not null,
    role       ENUM('seeker','employer') not null default 'seeker',
    created_at Timestamp Default CURRENT_TIMESTAMP
);

CREATE TABLE jobs (
    id          int AUTO_INCREMENT PRIMARY KEY,
    user_id int not null,
    title       nvarchar(150) not null,
    company     nvarchar(150) not null,
    location    nvarchar(150) not null,
    type        ENUM('Full-Time','Part-Time','Remote','Internship') default 'Full-Time',
    description text not null,
    salary      nvarchar(100),
    created_at  Timestamp Default CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) on delete cascade
);

CREATE TABLE applications (
    id           int AUTO_INCREMENT PRIMARY KEY,
    job_id       int not null,
    user_id    int not null,
    cover_letter text,
    status       ENUM('pending','reviewed','accepted','rejected') default 'pending',
    applied_at   Timestamp Default CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id)  REFERENCES jobs(id) on delete cascade,
    FOREIGN KEY (user_id) REFERENCES users(id) on delete cascade
);
