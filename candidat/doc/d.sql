CREATE TABLE journal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(255) NOT NULL,
    utilisateur VARCHAR(255) NOT NULL,
    date_heure DATETIME NOT NULL,
    objet VARCHAR(255) NOT NULL,
    description TEXT
);
