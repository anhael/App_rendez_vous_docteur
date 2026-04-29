# Theia test technique

## Introduction
> Ce test est à effectuer dans une limite de temps d'une heure (hors installation du projet).
> 
> Tu dois implémenter les versions dans l'ordre.
> 
> Les objectifs de ce test sont :
> - d'avoir une idée de ta manière d'aborder les problématiques
> - de servir de support pour nos échanges ultérieurs
> 
> La quantité de fonctionnalités implémentées n'est pas l'objectif principal du test, nous aurons le temps d'échanger par la suite sur ce qui n'a pas été fait ou non.
> 
> A noter qu'il n'est pas nécessaire dans ce test d'implémenter une gestion des utilisateurs, toutes les actions pourront être effectuées sans authentification.
>
> Pour simplifier la prise en main les outils docker/symfony sont déjà en place, mais si nécessaire pour pouvez les ajuster ou les améliorer.
 
## Version 1
>- [ ] Création du schéma de la base de données selon le jeu de données de sujets d'examen (fichier .csv dans le dossier data provenant de https://www.data.gouv.fr/fr/datasets/sujets-de-philosophie-du-baccalaureat/)
>- [ ] Créer une commande qui importe les données du CSV dans une base de données PostgreSQL
>- [ ] Afficher la liste des sujets avec une possibilité de les filtrer par notion
>- [ ] La liste doit contenir le sujet, l'année et les notions

## Version 2
>- [ ] Ajouter la possibilité de modifier les notions aux sujets existants (ajout et suppression)

## Version 3
>- [ ] Permettre à l'utilisateur de répondre à un sujet, pour valider sa copie, il devra entrer son nom et la réponse au sujet dans 2 champs libres

## Version 4
>- [ ] Permettre à l'utilisateur de noter une copie

## Premier pas
1. ```docker compose up --build```
2. Modifier le fichier .env pour utiliser la BDD du docker-compose, `database` au lieu de `127.0.0.1`
3. Allez sur [localhost](https://localhost:4433)


--------------------------------------------------------------


Création entité  Etudiant correpondants aux contenus du CSV
Création Migration pour les tables et pour pouvoir stocker les données du CSV dans la bdd
Création commande symfony 'app:import-csv' pour insérer les lignes du CSV dans la table
Création controller SujetController pour filtrer les données et affichage du liste avec filtre et pagination
Création fonction crud pour pouvroi ajouter et supprimer le notion d'un sujet
Crétion fonctionnalité pour répondre et valider la réponse en ajoutant le nom et la réponse 
Crétion bouton pour voir les copies valdés et ajout possibilité de noter les copies


⏰ Aujourd'hui: 28 Avril 2026, 14:00

1. Patient va sur http://localhost:4433
2. Se connecte: patient@medicline.local / Patient123!
3. Dropdown "Sélectionner une clinique" → "Clinique de Paris"
4. Voit les médecins: Dr. Laurent (Cardio), Dr. Sophie (Pédiatre)
5. Clic "Dr. Laurent"
6. Calendrier affiche:
   - Aujourd'hui 28/04: Créneaux 14:00-18:00 (car 14:00 actuellement, slots après OK)
   - Demain 29/04: Créneaux 09:00-12:00, 14:00-18:00
7. Clic "Demain 29/04 à 10:00" ✅
8. Formulaire:
   - Date: 2026-04-29
   - Heure: 10:00
   - Durée: 30 min
9. Clic "Confirmer" → Message: "Rendez-vous enregistré" ✅
10. RDV apparaît dans "Mes rendez-vous" avec 🟡 Pending

1. Admin se connecte: admin@medicline.local / Admin123!
2. Onglet "Rendez-vous"
3. Voit: Jean Dupont | Dr. Laurent | 29/04 10:00 | Pending 🟡
4. Clic → Change status à "confirmed"
5. Clic "Valider" → Statut devient 🟢 Confirmed