-- =====================================================
-- NewsLanka Database Schema
-- SE102.3 Web Based Application Development Assignment
-- =====================================================

DROP DATABASE IF EXISTS newslanka;
CREATE DATABASE newslanka DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE newslanka;

-- -----------------------------------------------------
-- Table: users
-- -----------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: categories
-- -----------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: articles
-- -----------------------------------------------------
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL UNIQUE,
    excerpt VARCHAR(500),
    content LONGTEXT NOT NULL,
    image VARCHAR(255),
    category_id INT NOT NULL,
    author_id INT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    is_featured TINYINT(1) DEFAULT 0,
    views INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: comments
-- -----------------------------------------------------
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: contact_messages
-- -----------------------------------------------------
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: tags
-- -----------------------------------------------------
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE article_tags (
    article_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- SEED DATA
-- =====================================================

-- Default admin user (password: admin123)
-- Default test user (password: user123)
-- Passwords use PHP password_hash() with PASSWORD_BCRYPT
INSERT INTO users (name, email, password, role, bio) VALUES
('Site Administrator', 'admin@newslanka.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Chief Editor and Administrator of NewsLanka'),
('Saman Perera', 'saman@newslanka.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Senior reporter covering local Sri Lankan news');

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Politics', 'politics', 'Political news from Sri Lanka and around the world'),
('Sports', 'sports', 'Latest sports news, cricket, football and more'),
('Technology', 'technology', 'Tech news, gadgets, AI and innovation'),
('Business', 'business', 'Business, economy and finance news'),
('Local', 'local', 'Sri Lankan local news and current affairs'),
('Entertainment', 'entertainment', 'Movies, music, celebrities and entertainment news');

-- Tags
INSERT INTO tags (name, slug) VALUES
('Breaking', 'breaking'),
('Sri Lanka', 'sri-lanka'),
('Cricket', 'cricket'),
('AI', 'ai'),
('Economy', 'economy'),
('Cinema', 'cinema');

-- Sample articles
INSERT INTO articles (title, slug, excerpt, content, image, category_id, author_id, status, is_featured) VALUES
(
    'Sri Lanka Cricket Team Wins Historic Series Against Australia',
    'sri-lanka-cricket-wins-australia',
    'Sri Lankan cricket team secured a memorable victory in the test series, marking a new chapter in the nation''s cricketing history.',
    '<p>In a thrilling display of skill and determination, the Sri Lankan cricket team has clinched a historic test series victory against Australia at the iconic Sinhalese Sports Club Ground in Colombo.</p><p>The decisive fifth-day performance saw the home team chase down a challenging target of 285 runs, with captain leading from the front with an unbeaten century. The victory marks the first home series win against Australia in over two decades.</p><p>"This is a moment we will cherish forever," said the team captain at the post-match press conference. "The crowd support throughout the series was incredible, and this win is dedicated to every Sri Lankan cricket fan."</p><p>The series victory is expected to boost the team''s ranking and morale ahead of the upcoming international tournaments.</p>',
    'cricket-default.jpg',
    2, 1, 'published', 1
),
(
    'New Tech Hub Launched in Colombo to Boost IT Industry',
    'new-tech-hub-colombo-it-industry',
    'A state-of-the-art technology hub has been inaugurated in Colombo, aiming to position Sri Lanka as a leading IT destination in South Asia.',
    '<p>The government has officially launched the largest technology innovation hub in the country, located in the heart of Colombo. The facility spans over 50,000 square feet and will house startups, established tech companies, and research labs.</p><p>The hub aims to attract foreign investment and provide infrastructure for over 200 tech companies. It features modern co-working spaces, conference rooms, and dedicated zones for AI research, blockchain development, and cybersecurity.</p><p>Industry leaders have welcomed the initiative, noting that it addresses a long-standing need for centralized tech infrastructure. The facility is expected to create over 5,000 jobs within the next two years.</p>',
    'tech-default.jpg',
    3, 1, 'published', 1
),
(
    'Parliament Passes New Economic Reform Bill',
    'parliament-passes-economic-reform-bill',
    'The Sri Lankan Parliament has approved sweeping economic reforms aimed at stabilizing the national economy.',
    '<p>After weeks of debate, Parliament has passed the much-anticipated Economic Reform Bill with a clear majority. The bill introduces significant changes to taxation, foreign investment policies, and state enterprise management.</p><p>Key provisions include tax incentives for export-oriented industries, simplified business registration processes, and a roadmap for privatization of select state enterprises. The reforms are expected to attract foreign direct investment and create employment opportunities.</p><p>Economic analysts have given the bill a cautious welcome, noting that successful implementation will be key to realizing its potential benefits.</p>',
    'politics-default.jpg',
    1, 1, 'published', 0
),
(
    'AI Revolution: How Machine Learning is Changing Sri Lankan Businesses',
    'ai-revolution-sri-lankan-businesses',
    'Sri Lankan businesses are increasingly adopting artificial intelligence to enhance productivity and customer service.',
    '<p>From banking to retail, artificial intelligence and machine learning are transforming how Sri Lankan businesses operate. Major banks have deployed AI-powered chatbots, while e-commerce platforms use ML algorithms for personalized recommendations.</p><p>Local startups are also riding the AI wave. A recent survey indicates that over 40% of medium-to-large enterprises in Colombo have implemented some form of AI technology in the past year.</p><p>Experts predict that AI adoption will accelerate further, with the government planning a national AI strategy to be released later this year.</p>',
    'tech-default.jpg',
    3, 2, 'published', 0
),
(
    'Local Tourism Sector Records Strong Recovery',
    'local-tourism-strong-recovery',
    'Sri Lanka''s tourism industry is showing remarkable signs of recovery with record arrivals this quarter.',
    '<p>The Sri Lanka Tourism Development Authority has announced that tourist arrivals have reached pre-pandemic levels, with over 250,000 visitors recorded in the past month alone.</p><p>Popular destinations such as Sigiriya, Galle, and Ella have reported full bookings throughout the season. The recovery is attributed to improved infrastructure, targeted marketing campaigns, and the easing of travel restrictions globally.</p><p>Tourism officials are optimistic that the industry will exceed previous records by year-end, contributing significantly to foreign exchange earnings.</p>',
    'local-default.jpg',
    5, 2, 'published', 0
),
(
    'Box Office Hit: New Sinhala Film Breaks Records',
    'new-sinhala-film-breaks-records',
    'A locally produced Sinhala film has become the highest-grossing movie of the year, drawing massive crowds nationwide.',
    '<p>The newly released Sinhala film has shattered box office records, earning over Rs. 50 million in its opening week. Cinema halls across the country reported houseful screenings.</p><p>The film, directed by an acclaimed local filmmaker, tells a contemporary story that resonates with audiences. Critics have praised the storytelling, cinematography, and performances.</p><p>The success marks a significant moment for the local film industry, demonstrating the strong appetite for quality Sinhala cinema among Sri Lankan audiences.</p>',
    'entertainment-default.jpg',
    6, 1, 'published', 0
);

-- Article-tag relationships
INSERT INTO article_tags (article_id, tag_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 2), (2, 4),
(3, 2), (3, 5),
(4, 4), (4, 2),
(5, 2),
(6, 6), (6, 2);

-- Sample comments (approved)
INSERT INTO comments (article_id, user_id, comment, status) VALUES
(1, 2, 'Amazing victory! Proud moment for every Sri Lankan.', 'approved'),
(2, 2, 'Great initiative. This will surely boost the local tech ecosystem.', 'approved');
