<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Willa Team</title>
    <link rel="icon" type="image/png" href="grupa.png">

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300&family=Josefin+Sans:wght@150;200;300&display=swap"
      rel="stylesheet"
    />
    <style>
      body {
        font-family: "Josefin Sans", sans-serif;
        font-weight: bold;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 100vh;
        text-decoration: none;
        text-align: center;

        overflow-y: scroll; 
    overflow-x: hidden;
      }

      html,
      body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      nav {

    width: 100%;
    background-color: #02b1d9;
    border-bottom: 1px solid #e7e7e7;
    padding: 2px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
}

        nav a {
            margin: 0 10px;
            text-decoration: none;
            color: white;
            font-size: 0.9rem;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            flex-grow: 1;
        }


        .mobile-menu-button {
            display: none;
        }

        @media screen and (max-width: 768px) {
            .mobile-menu-button {
                display: block;
                position: absolute;
                align-items: center;
                right: 20px;
                z-index: 1000;
                cursor: pointer;
                background: transparent;
                border: none;

            }

            .bar {
                width: 25px;
                height: 3px;
                background-color: white;
                margin: 5px 0;
                transition: 0.4s;
            }

            .mobile-menu-button.open .bar:nth-child(1) {
                transform: rotate(-45deg) translate(-5px, 6px);
            }
            .mobile-menu-button.open .bar:nth-child(2) {
                opacity: 0;
            }
            .mobile-menu-button.open .bar:nth-child(3) {
                transform: rotate(45deg) translate(-5px, -6px);
            }

            .nav-links {
    flex-direction: column;
    position: absolute;
    top: 68px;
    left: 0;
    right: 0;
    background-color: #02b1d9;

    text-align: center;
    max-height: 0;
    overflow: hidden;

}

            .nav-links a {
                margin: 10px 0;
            }
            .mobile-menu-button.open + .nav-links {
    max-height: 500px; 
}
            .nav-links {
    transition: max-height 0.6s ease-in-out; 
    overflow: hidden; 
    max-height: 0;
 
}

.mobile-menu-button.open + .nav-links {
    max-height: 1000px; 

}

        }






        main {
            flex: 1;
            display: flex;
            flex-direction: column; 
            align-items: center; 
            justify-content: flex-start;
            width: 100%;
            padding: 20px; 
        }
        table {
        max-width: 1000px;
        text-align: center;

        width: 100%;
        border-collapse: collapse;
        margin: 20px auto;
        margin-bottom: 4rem;
        border: solid 1px #8f8f8f;
        font-size: 0.8rem;
    }

    th, td {
        padding: 10px;
        text-align: center;
        border: solid 1px #8f8f8f;
    }

    td span {
        font-weight: bold;
    }

    th {
        background-color: #02b1d9;
        border-bottom: 1px solid #8f8f8f;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #ececec;
    }

    @media screen and (max-width: 768px) {
        table {
            font-size: 0.7rem; 
            margin-bottom: 2rem;
            width: 95%;
        }

        th, td {
            padding: 8px; 
        }

        
    }

      .custom-form {
    max-width: 300px;
    margin: 0 auto;
    text-align: center;
  }

 
  .custom-form label {
    display: block;
    margin-bottom: 10px;
    font-size: 18px;
    color: #333;
    text-align: left;
    box-sizing: border-box; 
  }

  
  .custom-form input[type="month"] {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    outline: none;
    margin-bottom: 5px;
    box-sizing: border-box; 
  }


  .custom-form input[type="submit"] {
    display: block;
    width: 100%;
    padding: 10px;
    font-size: 18px;
    background-color: transparent;
    color: #02b1d9;
    border: 1px solid #02b1d9;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 5px;
    box-sizing: border-box; 
  }
  .custom-form input[type="submit"]:hover {
            background-color: #02b1d9;
            color: #fff;
        }

  select {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        outline: none;
    }


    select option {
        font-size: 16px;
        color: #333;
    }


    select option[selected] {
        background-color: #007bff; 
        color: #fff; 
    }
    
    .survey-item {
            padding: 15px 0;
            border-bottom: 1px solid #02b1d9;
        }

        .survey-item:last-child {
            border-bottom: none;
        }

        .survey-item strong {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .survey-item a {
    color: #02b1d9;
    text-decoration: none;
    margin: 0 10px;
    padding: 5px 0; 
    border: 1px solid #02b1d9;
    border-radius: 5px;
    transition: background-color 0.3s;
    display: inline-block;
    width: 150px; 
    text-align: center; 
}


        .survey-item a:hover {
            background-color: #02b1d9;
            color: #fff;
        }
.date-form {
    background-color: #f0f8ff; 
            border: 1px solid #d1e0e0;
            border-radius: 5px;
            padding: 10px;

            font-size: 1.1em;
            width: 350px;
            margin: 20px auto;
}
.date-form h1 {

color: #333;
text-align: center;
margin-bottom: 20px;
font-size: 1.2em;
border-bottom: 2px solid #02b1d9;
padding-bottom: 10px;
letter-spacing: 1px; 
}

.menu-item {
    display: flex;
    align-items: center;
    text-align: center;
}


.nav-links a img {
    vertical-align: middle;
    height: 25px;
    width: auto;
    margin-right: 5px;
}


@media screen and (max-width: 768px) {
    .nav-links {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .nav-links a {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%; 
        padding: 3px 0; 
    }
}

.date-time {
    color: #fff; 
    font-size: 1rem; 
    padding: 10px; 

    text-align: center;
    justify-content: center
}
.date-time {
    width: 100px; 
    overflow: hidden; 
    white-space: nowrap; 

}


@media screen and (max-width: 768px) { 
    

    .nav-links, .date-time {
        order: 1; 
        width: 100%;
        text-align: center; 
    }
    .date-time {
        margin-top: 10px;
        margin-right: 70px;
    }


}


    </style>
</head>
<body>
    <nav>
    <a href="<?php echo (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="logo">
        <img src="willa2.png" alt="Logo" style="width: 60px; height: auto;">
    </a>

        <button id="mobile-menu-button" class="mobile-menu-button">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </button>
        <div class="nav-links">
            <?php 

            if (isset($_SESSION['is_admin'])) {
                if ($_SESSION['is_admin'] == 1) {
   
                    echo '<a href="admin_dashboard.php"><span class="menu-item" style="color: red;"><img src="images/admin.png" alt="">Panel administratora</span></a>
                    <a href="users.php"<span class="menu-item"><img src="images/users.png" alt="">Użytkownicy</a>
                    
                    <a href="shifts.php"<span class="menu-item"><img src="images/shifts.png" alt="">Zmiany</a>
                    <a href="tips.php"<span class="menu-item"><img src="images/money-bag.png" alt="">Napiwki</a>
                    <a href="create_schedule.php"><span class="menu-item"><img src="images/schedule1.png" alt="">Ustaw grafik</span></a>
                    <a href="all_schedule.php"><span class="menu-item"><img src="images/schedule1.png" alt="">Wszystkie grafiki</span></a>
                    <a href="categories.php"><span class="menu-item"><img src="images/check1.png" alt="">Checklist</span></a>
                    <a href="notes.php"><span class="menu-item"><img src="images/info1.png" alt="">Info</span></a>
                    <a href="logout.php"><span class="menu-item"><img src="images/logout.png" alt="">Wyloguj się</span></a>';
          } elseif ($_SESSION['is_admin'] == 2) {

              echo '<a href="user_dashboard.php"><span class="menu-item"><img src="images/dashboard.png" alt="">Panel Użytkownika</span></a>
                    
                    <a href="schedule.php"><span class="menu-item"><img src="images/schedule.png" alt="">Mój grafik</span></a>
                    <a href="full_schedule.php"><span class="menu-item"><img src="images/schedule1.png" alt="">Pełny grafik</span></a>
                    <a href="view_hours.php"><span class="menu-item"><img src="images/clock.png" alt="">Godziny pracy</span></a>
                    <a href="my_surveys.php"><span class="menu-item"><img src="images/checklist.png" alt="">Checklist</span></a>
                    <a href="messages.php"><span class="menu-item"><img src="images/message.png" alt="">Wiadomości</span></a>
                    <a href="create_schedule.php"><span class="menu-item"><img src="images/schedule.png" alt="">Ustaw grafik</span></a>
                    <a href="tips.php"<span class="menu-item"><img src="images/money-bag.png" alt="">Napiwki</a>
                    <a href="my_profile.php"><span class="menu-item"><img src="images/user.png" alt="">Mój profil</span></a>
                    <a href="logout.php"><span class="menu-item"><img src="images/logout.png" alt="">Wyloguj się</span></a>';
          } else {
 
              echo '<a href="user_dashboard.php"><span class="menu-item"><img src="images/dashboard.png" alt="">Panel Użytkownika</span></a>
                    <a href="schedule.php"><span class="menu-item"><img src="images/schedule.png" alt="">Mój grafik</span></a>
                    <a href="full_schedule.php"><span class="menu-item"><img src="images/schedule1.png" alt="">Pełny grafik</span></a>
                    <a href="view_hours.php"><span class="menu-item"><img src="images/clock.png" alt="">Godziny pracy</span></a>
                    <a href="my_surveys.php"><span class="menu-item"><img src="images/checklist.png" alt="">Checklist</span></a>
                    <a href="messages.php"><span class="menu-item"><img src="images/message.png" alt="">Wiadomości</span></a>
                    <a href="my_profile.php"><span class="menu-item"><img src="images/user.png" alt="">Mój profil</span></a>
                    <a href="logout.php"><span class="menu-item"><img src="images/logout.png" alt="">Wyloguj się</span></a>';
                }
            }
            ?>
            
        </div>
        <div class="date-time">
        <div id="date"></div>
        <div id="time"></div>
    </div>
        
    </nav>

    <script>
const mobileMenuButton = document.getElementById('mobile-menu-button');
const navLinks = document.querySelector('.nav-links');

mobileMenuButton.addEventListener('click', () => {
    mobileMenuButton.classList.toggle('open');
});
    </script>
<script>
function updateDateTime() {
    var now = new Date();
    var year = now.getFullYear();
    var month = (now.getMonth() + 1).toString().padStart(2, '0');
    var day = now.getDate().toString().padStart(2, '0');
    var date = year + '-' + month + '-' + day;

    var hours = now.getHours().toString().padStart(2, '0');
    var minutes = now.getMinutes().toString().padStart(2, '0');
    var seconds = now.getSeconds().toString().padStart(2, '0');
    var time = hours + ':' + minutes + ':' + seconds;

    document.getElementById('date').innerText = date;
    document.getElementById('time').innerText = time;
}

setInterval(updateDateTime, 1000); 
updateDateTime(); 
</script>


</body>
</html>