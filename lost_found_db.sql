-- --------------------------------------------------------
-- Database: lost_found_db
-- Description: SQL structure for Lost & Found Web Application
-- --------------------------------------------------------

-- Create the database
CREATE DATABASE IF NOT EXISTS lost_found_db;
USE lost_found_db;

-- --------------------------------------------------------
-- Table structure for items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('Lost', 'Found') NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Insert sample data
-- --------------------------------------------------------
INSERT INTO items (title, description, category, image) VALUES
('Black Wallet', 'A black leather wallet lost near the library.', 'Lost', 'wallet.jpg'),
('Water Bottle', 'Blue water bottle found in the canteen.', 'Found', 'water_bottle.jpg'),
('Keys', 'Set of keys with a red keychain.', 'Lost', 'keys.jpg');
