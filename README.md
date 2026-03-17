# CipFitStudio - Fitness Management Web App

## Overview

CipFitStudio este o aplicație web dezvoltată în PHP pentru administrarea unei săli de fitness, cu autentificare pe roluri și fluxuri dedicate pentru client, antrenor și administrator.

Demo: https://cvlad.daw.ssmr.ro/

## Tech Stack

- Backend: PHP (OOP), PDO, Composer
- Frontend: HTML, Tailwind CSS, JavaScript (Fetch API / AJAX)
- Database: MySQL (users, subscriptions, classes, class_registrations)
- Email: PHPMailer (verificare cont, resetare parolă, notificări)
- Reporting: FPDF (export PDF), CSV export
- Security/Validation: password hashing, token-uri cu hash, CAPTCHA custom, Google reCAPTCHA

## Architecture

- Model layer OOP pentru entitățile principale: User și FitnessClass.
- Data access layer centralizat prin OperatiiDB pentru operații CRUD reutilizabile.
- Role-based routing și dashboard-uri separate pentru client, antrenor și admin.
- Endpoint-uri JSON pentru acțiuni asincrone (auth, clase, înscrieri, statistici, contact).

## Security Highlights

- Parole stocate securizat cu password_hash / password_verify.
- Activare cont pe email folosind token generat server-side și stocat ca hash în baza de date.
- Flux de resetare parolă cu token unic și expirare temporală.
- CAPTCHA custom la înregistrare:
	- imagine generată dinamic cu forme/culori;
	- întrebări dinamice pe baza imaginii;
	- limitare la 3 încercări eșuate.
- Integrare Google reCAPTCHA pe formularul de contact.

## Features

- API intern pe JSON pentru login/signup, CRUD clase, înscrieri, editare profil, contact și statistici.
- Gestionare abonamente (Basic, Premium, VIP) cu reguli de business pentru număr maxim de clase active.
- Înscriere/renunțare la clase cu verificare capacitate disponibilă.
- Dashboard admin cu vizualizare statistici și export rapoarte în CSV/PDF.
- Interfață modernă și responsive pentru toate fluxurile principale.

## Roluri în aplicație

### Client

- își alege și modifică abonamentul (Basic, Premium, VIP)
- vede toate clasele disponibile și detaliile acestora
- se înscrie/renunță la clase, în limita abonamentului și a locurilor disponibile

### Antrenor

- creează clase noi (titlu, descriere, dată, oră, durată, capacitate, locație)
- vizualizează toate clasele proprii într-un dashboard dedicat
- editează sau șterge clasele create

### Admin

- vizualizează statistici despre abonamente și înscrieri
- generează export de statistici în format CSV și PDF
- monitorizează activitatea generală a platformei din dashboard-ul de administrare
