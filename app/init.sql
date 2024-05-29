-- Créer la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS e_commerce;

-- Créer l'utilisateur si celui-ci n'existe pas
DO $$ BEGIN
   CREATE USER IF NOT EXISTS postgres WITH PASSWORD 'postgres';
EXCEPTION WHEN duplicate_object THEN null; END $$;

-- Modifier le mot de passe de l'utilisateur existant
ALTER USER postgres WITH PASSWORD 'postgres';
