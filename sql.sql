-- Create the database
CREATE DATABASE portfolio;

-- Use the database
USE portfolio;

-- Create the admin table
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL -- Password should be hashed for security
);

-- Create the contact table
CREATE TABLE contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL, -- Path or URL of the project image
    category VARCHAR(100) NOT NULL,
    github_link VARCHAR(255) NOT NULL -- URL of the GitHub repository
);

-- Insert sample data into admin table
INSERT INTO admin (user, password)
VALUES 
('semir', '1212'), -- Replace 'password123' with a hashed version for production
('ss', 'ss1212');

-- Insert sample data into contact table
INSERT INTO contact (name, email, subject, message)
VALUES 
('Alice', 'alice@example.com', 'Inquiry', 'I would like to know more about your services.'),
('Bob', 'bob@example.com', 'Feedback', 'Your portfolio is amazing!'),
('Charlie', 'charlie@example.com', 'Support', 'I need help with accessing your portfolio.');
