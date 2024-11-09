Projet Checklist : Gestion et Partage des Bonnes Pratiques

Présentation
  Ce projet a pour objectif d'améliorer la sécurité et l'efficacité des tests avioniques en identifiant et en partageant les bonnes pratiques. Nous avons développé une application web sécurisée et conviviale permettant de définir,   
  visualiser et exporter ces pratiques sous forme de fichiers CSV, PDF et Excel.

Technologies Utilisées
  Front-end : HTML, CSS, JavaScript
  Back-end : PHP, Python
  Base de Données : MySQL, gérée via phpMyAdmin
  Serveur Web : Apache
  Environnement de Développement : Raspberry Pi 3, Visual Studio Code, GitHub

Installation
  Pré-requis
    Raspberry Pi 3
    Carte microSD (16 Go ou plus)
    Clavier, souris, écran et câble HDMI
    Connexion Internet
  Étapes
    Téléchargement et Installation de Raspberry Pi OS :
      Téléchargez le Raspberry Pi Imager depuis le site officiel.
      Préparez la carte microSD avec le système d'exploitation Raspberry Pi OS.
    
    Configuration Initiale du Raspberry Pi :
      Connectez le Raspberry Pi à l'écran, clavier, souris et à Internet.
      Modifiez les paramètres régionaux et changez le mot de passe par défaut.
    
    Mise à Jour du Système et Installation des Applications :
      sudo apt update
      sudo apt upgrade
      sudo apt install apache2 php libapache2-mod-php mariadb-server php-mysql phpmyadmin
      sudo service apache2 restart
      sudo pip install pandas xlsxwriter fpdf
      sudo python3 -m pip install numpy
      sudo apt-get install libopenblas-base
    
    Configuration de la Base de Données :
      Téléchargez le fichier SQL depuis GitHub et importez-le dans phpMyAdmin.
      Configurez les utilisateurs et les permissions dans MariaDB.
    
    Téléchargement du Site Web et Déploiement :
      Téléchargez le dossier Site_web_VF depuis GitHub et placez les fichiers dans /var/www/html.
      Vérifiez que les dépendances Python nécessaires sont installées.
      
    Configuration de l'Adresse IP Statique :
      Ouvrez les paramètres réseau et configurez l'adresse IP, le masque de sous-réseau, la passerelle et les serveurs DNS.

  Fonctionnalités
    Pour les Utilisateurs
      Sélection de Programmes et Phases : Visualisez les bonnes pratiques associées.
      Recherche par Mots-Clés : Filtrez les bonnes pratiques.
      Gestion des Bonnes Pratiques : Créez, dupliquez et supprimez des bonnes pratiques.
    Pour les Administrateurs
      Gestion des Comptes : Créez, modifiez et supprimez des comptes utilisateurs.
      Historique des Interactions : Visualisez les actions des utilisateurs.
      Gestion des Programmes : Créez et supprimez des programmes et phases.
    Pour les Super-Administrateurs
      Gestion des Droits : Attribuez ou retirez les droits d'administrateur.
      Accès Complet : Effectuez toutes les actions possibles pour les administrateurs.
Sécurité
  Sessions PHP et Requêtes Préparées : Prévenez les injections SQL.
  Chiffrement des Mots de Passe : Utilisez le hashage SHA-256.
  Blocage après Tentatives de Connexion Echouées : Bloquez les comptes après trois échecs.

Assurance Qualité
  Plan de Validation
    Création de Compte : Vérification de l'unicité, des exigences de mot de passe et du chiffrement.
    Gestion des Utilisateurs : Contrôle des accès et permissions.
    Tests d'Interface Web : Vérification de l'ergonomie et des fonctionnalités.
  
  Procédures de Test
    Tests Unitaires et d'Intégration : Vérification des fonctionnalités individuelles et de leur intégration.
    Rapports de Test et Fiches d'Anomalies : Documentation des anomalies et des corrections apportées.

Gestion de Configuration
  GitHub : Utilisation pour la gestion du code source, des commits, des branches et des tags.
  Versions et Tags : Marquage des versions importantes du projet.

Consommation et Impact Environnemental
  Consommation Annuelle : Estimation de la consommation énergétique du Raspberry Pi.
  Impact Environnemental : Calcul des émissions de CO2 en fonction de la consommation énergétique.

Conclusion
  Le projet Checklist a été conçu pour améliorer les processus de test avioniques en fournissant une plateforme sécurisée et efficace pour gérer les bonnes pratiques. Ce projet a permis de développer des compétences techniques en         
  programmation, gestion de bases de données, et gestion de projets collaboratifs.

Annexes :
  Pour plus de détails, consultez le rapport complet.
