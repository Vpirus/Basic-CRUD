/*CREATE TABLE events (
    evCode INT AUTO_INCREMENT PRIMARY KEY,
    evName VARCHAR(255) NOT NULL,
    evDate DATE NOT NULL,
    evVenue VARCHAR(255),
    evRFree DECIMAL(10,2) DEFAULT 0
);

CREATE TABLE participants (
    partID INT AUTO_INCREMENT PRIMARY KEY,
    evCode INT NOT NULL,
    partFName VARCHAR(100) NOT NULL,
    partLName VARCHAR(100) NOT NULL,
    partDRate DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (evCode) REFERENCES events(evCode) ON DELETE CASCADE
);

CREATE TABLE registrations (
    regCode INT AUTO_INCREMENT PRIMARY KEY,
    partID INT NOT NULL,
    regDate DATE NOT NULL,
    regFPaid DECIMAL(10,2) DEFAULT 0,
    regPMode VARCHAR(50),
    FOREIGN KEY (partID) REFERENCES participants(partID) ON DELETE CASCADE
);*/

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS registration_db;
USE registration_db;

-- Step 2: Create events table
CREATE TABLE IF NOT EXISTS events (
    evCode INT AUTO_INCREMENT PRIMARY KEY,
    evName VARCHAR(255) NOT NULL,
    evDate DATE NOT NULL,
    evVenue VARCHAR(255) NOT NULL,
    evRFree DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 3: Create participants table
CREATE TABLE IF NOT EXISTS participants (
    partID INT AUTO_INCREMENT PRIMARY KEY,
    evCode INT NOT NULL,
    partFName VARCHAR(100) NOT NULL,
    partLName VARCHAR(100) NOT NULL,
    partDRate DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evCode) REFERENCES events(evCode) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 4: Create registrations table
CREATE TABLE IF NOT EXISTS registrations (
    regCode INT AUTO_INCREMENT PRIMARY KEY,
    partID INT NOT NULL,
    regDate DATE NOT NULL,
    regFPaid DECIMAL(10, 2) DEFAULT 0.00,
    regPMode VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partID) REFERENCES participants(partID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
