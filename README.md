# README - VV MBO Soccer

Welkom bij het officiële clubportaal van **Voetbalvereniging MBO Soccer**, ontwikkeld voor de module **Webdev 3 – OOP 1**.

---

## 🚀 Snel aan de slag (Docker Setup)

Het project draait volledig in Docker via `docker compose`:

```bash
# Containers opstarten
docker compose up -d

# Status controleren
docker compose ps
```

De services zijn direct bereikbaar via:
- 🌐 **MBO Soccer Website:** [http://localhost:6767](http://localhost:6767)
- 🗄️ **phpMyAdmin:** [http://localhost:6969](http://localhost:6969) (Server: `db`, Gebruiker: `root`, Wachtwoord: `supergeheim`)
- 🐬 **MySQL Database:** poort `3306` (Database: `phples`)

---

## 🔑 Standaard Testaccounts

Alle accounts zijn standaard voorzien van veilige bcrypt password hashes:

| Rol | E-mailadres | Wachtwoord | Rechten & Mogelijkheden |
| :--- | :--- | :--- | :--- |
| **Clubmedewerker (Admin)** | `admin@mbosoccer.nl` | `Admin123!` | Volledige toegang: Ledenbeheer (CRUD), Wedstrijdbeheer (CRUD), Teams & Opstellingen, Aanwezigheidsoverzicht. |
| **Trainer / Coach** | `trainer@mbosoccer.nl` | `Trainer123!` | Wedstrijdbeheer, Teams & Opstellingen, Aanwezigheid inzien en controleren. |
| **Lid / Speler** | `speler@mbosoccer.nl` | `Speler123!` | Eigen profiel beheren, Programma inzien, Realtime aanwezigheid doorgeven ('wel/niet aanwezig'). |

---

## 📂 Projectstructuur & OOP Architectuur

```
mbosoccer/
├── config/
│   └── Database.php          # Singleton PDO connectie met prepared statement enforcement
├── classes/                  # OOP Domeinmodels
│   ├── User.php              # Gebruikersbeheer, authenticatie en autorisatie
│   ├── Team.php              # Teambeheer en selecties
│   ├── MatchGame.php         # Wedstrijden CRUD en status
│   ├── Training.php          # Trainingsschema's
│   ├── Attendance.php        # Aanwezigheidsregistratie en analyses
│   └── Lineup.php            # Opstellingen en veldposities
├── docs/
│   └── PROJECTDOCUMENT.md    # Volledig projectdocument (Stories, Narratives, Diagrams, Sitemap, Wireframes)
├── sql/
│   ├── schema.sql            # Database tabellen en foreign keys
│   └── seed.sql              # Initieele testdata en demo-accounts
├── includes/
│   ├── header.php            # Navigatiebalk en gebruikersbadge
│   ├── footer.php            # Voettekst en scripts
│   └── auth_check.php        # Role-based access control (RBAC) & CSRF helpers
├── public/
│   ├── css/style.css         # Modern, responsief clubdesign (Groen / Navy)
│   └── js/
│       ├── main.js           # Client-side filtering en menu interactie
│       └── attendance.js     # Dynamische 'wel/niet aanwezig' toggler (Fetch API)
├── api/
│   └── set_attendance.php    # JSON API endpoint voor aanwezigheid
├── index.php                 # Homepage / Visual design met statistieken
├── login.php                 # Veilig inloggen
├── register.php              # Zelfregistratie voor nieuwe leden
├── profile.php               # Eigen account beheren en teamweergave
├── schedule.php              # Wedstrijdoverzicht & aanwezigheid doorgeven
└── admin/
    ├── dashboard.php         # Beheerders KPI-dashboard
    ├── matches.php           # Wedstrijdbeheer CRUD
    ├── teams.php             # Teams en selecties beheren
    ├── members.php           # Ledenbeheer CRUD (alleen medewerker)
    └── attendance.php        # Aanwezigheid per wedstrijd inzien
```

---

## 🛡️ Beveiliging

1. **SQL-injectie preventie:** Alle databasecommunicatie verloopt via `PDO` met prepared statements en gebonden parameters.
2. **XSS-bescherming:** Output wordt ge-escaped met `htmlspecialchars()`.
3. **Wachtwoorden:** Gehasht met `password_hash($pass, PASSWORD_BCRYPT)`.
4. **Autorisatie (RBAC):** Toegang tot `/admin/` pagina's is strikt afgeschermd op basis van de sessierol (`lid`, `trainer`, `medewerker`).
