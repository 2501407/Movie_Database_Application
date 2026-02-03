-- MOVIE DATABASE — SQL SETUP SCRIPT

USE np03cs4a240033;

-- USERS TABLE  (session-based auth)
CREATE TABLE IF NOT EXISTS users (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(191) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,           -- bcrypt hash (password_hash)
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- MOVIES TABLE

CREATE TABLE IF NOT EXISTS movies (
    id       INT          AUTO_INCREMENT PRIMARY KEY,
    title    VARCHAR(255) NOT NULL,
    year     SMALLINT     NOT NULL,
    genre    VARCHAR(150) NOT NULL,             -- comma-separated genres
    rating   DECIMAL(3,1) NOT NULL DEFAULT 0.0, -- e.g. 8.8
    director VARCHAR(150) NOT NULL,
    duration VARCHAR(20)  NOT NULL,             -- e.g. "2h 19m"
    cast     TEXT         DEFAULT NULL,         -- cast members
    synopsis TEXT         DEFAULT NULL          -- movie synopsis
);

-- SEED DATA 

TRUNCATE TABLE movies;

INSERT INTO movies (title, year, genre, rating, director, duration, cast, synopsis) VALUES
('The Shawshank Redemption',             1994, 'Drama',                          9.3, 'Frank Darabont',  '2h 22m', 'Tim Robbins, Morgan Freeman, Bob Gunton', 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.'),
('The Godfather Part II',                 1974, 'Crime, Drama',                   9.0, 'Francis Ford Coppola', '3h 22m', 'Al Pacino, Robert De Niro, Robert Duvall', 'The early life and career of Vito Corleone in 1920s New York City is portrayed, while his son, Michael, expands and tightens his grip on the family crime syndicate.'),
('The Dark Knight',                       2008, 'Action, Crime, Drama',           9.0, 'Christopher Nolan','2h 32m', 'Christian Bale, Heath Ledger, Aaron Eckhart', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.'),
('12 Angry Men',                          1957, 'Crime, Drama',                   9.0, 'Sidney Lumet',    '1h 36m', 'Henry Fonda, Lee J. Cobb, Martin Balsam', 'The jury in a New York City murder trial is frustrated by a single member whose skeptical caution forces them to more carefully consider the evidence before jumping to a hasty verdict.'),
('Pulp Fiction',                          1994, 'Crime, Drama',                   8.9, 'Quentin Tarantino','2h 34m', 'John Travolta, Uma Thurman, Samuel L. Jackson', 'The lives of two mob hitmen, a boxer, a gangster and his wife, and a pair of diner bandits intertwine in four tales of violence and redemption.'),
('The Lord of the Rings: The Return of the King', 2003, 'Action, Adventure, Drama', 9.0, 'Peter Jackson',   '3h 21m', 'Elijah Wood, Viggo Mortensen, Ian McKellen', 'Gandalf and Aragorn lead the World of Men against Sauron''s army to draw his gaze from Frodo and Sam as they approach Mount Doom with the One Ring.'),
('Forrest Gump',                          1994, 'Drama, Romance',                 8.8, 'Robert Zemeckis', '2h 22m', 'Tom Hanks, Robin Wright, Gary Sinise', 'The history of the US from the 1950s to the ''70s unfolds from the perspective of an Alabama man with an IQ of 75, who yearns to be reunited with his childhood sweetheart.'),
('Inception',                             2010, 'Action, Adventure, Sci-Fi',      8.8, 'Christopher Nolan','2h 28m', 'Leonardo DiCaprio, Joseph Gordon-Levitt, Elliot Page', 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.'),
('The Matrix',                            1999, 'Action, Sci-Fi',                 8.7, 'Lana Wachowski, Lilly Wachowski', '2h 16m', 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss', 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.'),
('Goodfellas',                            1990, 'Biography, Crime, Drama',        8.7, 'Martin Scorsese', '2h 25m', 'Robert De Niro, Ray Liotta, Joe Pesci', 'The story of Henry Hill and his life in the mob, covering his relationship with his wife Karen Hill and his mob partners Jimmy Conway and Tommy DeVito in the Italian-American crime syndicate.'),
('One Flew Over the Cuckoo''s Nest',      1975, 'Drama',                          8.7, 'Milos Forman',    '2h 13m', 'Jack Nicholson, Louise Fletcher, Michael Douglas', 'A criminal pleads insanity and is admitted to a mental institution, where he rebels against the oppressive nurse and rallies up the scared patients.'),
('Se7en',                                 1995, 'Crime, Drama, Mystery',          8.6, 'David Fincher',   '2h 7m', 'Morgan Freeman, Brad Pitt, Kevin Spacey', 'Two detectives, a rookie and a veteran, hunt a serial killer who uses the seven deadly sins as his motives.'),
('The Silence of the Lambs',              1991, 'Crime, Drama, Thriller',         8.6, 'Jonathan Demme',  '1h 58m', 'Jodie Foster, Anthony Hopkins, Scott Glenn', 'A young F.B.I. cadet must receive the help of an incarcerated and manipulative cannibal killer to help catch another serial killer, a madman who skins his victims.'),
('City of God',                           2002, 'Crime, Drama',                   8.6, 'Fernando Meirelles, Kátia Lund', '2h 10m', 'Alexandre Rodrigues, Leandro Firmino, Phellipe Haagensen', 'In the slums of Rio, two kids'' paths diverge as one struggles to become a photographer and the other a kingpin.'),
('It''s a Wonderful Life',                1946, 'Drama, Family, Fantasy',         8.6, 'Frank Capra',     '2h 10m', 'James Stewart, Donna Reed, Lionel Barrymore', 'An angel is sent from Heaven to help a desperately frustrated businessman by showing him what life would have been like if he had never existed.'),
('Interstellar',                          2014, 'Adventure, Drama, Sci-Fi',       8.6, 'Christopher Nolan','2h 49m', 'Matthew McConaughey, Anne Hathaway, Jessica Chastain', 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity''s survival.'),
('Léon: The Professional',                1994, 'Action, Crime, Drama',           8.5, 'Luc Besson',      '2h 10m', 'Jean Reno, Gary Oldman, Natalie Portman', 'Mathilda, a 12-year-old girl, is reluctantly taken in by Léon, a professional assassin, after her family is murdered. An unusual relationship forms as she becomes his protégée.'),
('The Green Mile',                        1999, 'Crime, Drama, Fantasy',          8.6, 'Frank Darabont',  '3h 9m', 'Tom Hanks, Michael Clarke Duncan, David Morse', 'The lives of guards on Death Row are affected by one of their charges: a black man accused of child murder and rape, yet who has a mysterious gift.'),
('Once Upon a Time in the West',          1968, 'Western',                        8.5, 'Sergio Leone',    '2h 45m', 'Henry Fonda, Charles Bronson, Claudia Cardinale', 'A mysterious stranger with a harmonica joins forces with a notorious desperado to protect a beautiful widow from a ruthless assassin working for the railroad.'),
('Back to the Future',                    1985, 'Adventure, Comedy, Sci-Fi',      8.5, 'Robert Zemeckis', '1h 56m', 'Michael J. Fox, Christopher Lloyd, Lea Thompson', 'Marty McFly, a 17-year-old high school student, is accidentally sent thirty years into the past in a time-traveling DeLorean invented by his close friend, Dr. Emmett "Doc" Brown.'),
('Psycho',                                1960, 'Horror, Mystery, Thriller',      8.5, 'Alfred Hitchcock', '1h 49m', 'Anthony Perkins, Janet Leigh, Vera Miles', 'A Phoenix secretary embezzles $40,000 from her employer''s client, goes on the run and checks into a remote motel run by a young man under the domination of his mother.'),
('The Prestige',                          2006, 'Drama, Mystery, Sci-Fi',         8.5, 'Christopher Nolan','2h 10m', 'Christian Bale, Hugh Jackman, Scarlett Johansson', 'After a tragic accident, two stage magicians engage in a battle to create the ultimate illusion while sacrificing everything they have to outwit each other.'),
('The Lion King',                         1994, 'Animation, Adventure, Drama',    8.5, 'Roger Allers, Rob Minkoff', '1h 28m', 'Matthew Broderick, Jeremy Irons, James Earl Jones', 'Lion prince Simba and his father are targeted by his bitter uncle, who wants to ascend the throne himself.'),
('Apocalypse Now',                        1979, 'Drama, Mystery, War',            8.4, 'Francis Ford Coppola', '2h 34m', 'Martin Sheen, Marlon Brando, Robert Duvall', 'A U.S. Army officer serving in Vietnam is tasked with assassinating a renegade Special Forces Colonel who sees himself as a god.'),
('Memento',                               2000, 'Mystery, Thriller',              8.4, 'Christopher Nolan','1h 53m', 'Guy Pearce, Carrie-Anne Moss, Joe Pantoliano', 'A man with short-term memory loss attempts to track down his wife''s murderer, using an intricate system of notes and tattoos to guide him.');