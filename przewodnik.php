
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Przewodnik</title>
    <link rel="icon" type="image/png" href="grupa.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .tut{
            background-color: #f0f8ff; 
            border: 1px solid #d1e0e0;
            border-radius: 5px;
            padding: 10px;

            font-size: 1.1em;
            max-width: 900px;
            margin: 20px auto;
        }
        .tut h1{
            color: #333;
text-align: center;
margin-bottom: 20px;
font-size: 1.2em;
border-bottom: 2px solid #02b1d9;
padding-bottom: 10px;
letter-spacing: 1px;
        }
        .tut h3 {
            color:#02b1d9;
            font-weight: bold;
        }
        .tut img {
            max-width: 500px;
            height: auto;
        }
        .tut p {
            margin: 5px;
        }

        @media screen and (max-width: 768px;) {
            .tut {
                width: 90%;
                margin: 10px auto;
            }
        }
        
        .custom-arrow {
      color: #02b1d9;
    }
    </style>
</head>
<body>
    <div class="tut">
        <h1>Przewodnik po aplikacji</h1>
        <br>
        <h4>Najważniejsze informacje dotyczące poruszania się po aplikacji oraz regulamin korzystania z czytnika.</h4>
        <br>

        <h3>1. Czytnik oraz godziny</h3>

        <p>Do aplikacji możemy zalogować się na 2 sposoby, za pomocą PIN'u oraz czytnika.</p>
        <p>W obu przypadkach musimy być na stronie logowania.</p>
        <br>
        <img src="/images/login.png" alt="logowanie">
        <br>
        <br>
        <p>Jeżeli ikona połączenia posiada niebieski kolor, możemy przyłożyć nasz brylok do czytnika, automatycznie nas zaloguje.</p>
        <br>
        <i class="fas fa-arrow-down custom-arrow"></i>
        <br>
        <img src="rfid_on1.png" alt="rfidon" style="width: 100px;">
        <br>
        <p>W przypadku, gdy ikona połączenia jest szara, nie możemy zalogować się za pomocą czytnika, wtedy wystarczy kliknąć na ikonę połączenia, aby móc się zalogować.</p>
        <br>
        <i class="fas fa-arrow-down custom-arrow"></i>
        <br>
        <img src="rfid_off7.png" alt="rfidon" style="width: 100px;">
        <br>
        <br>
        <p> <span style="color: green;">Rozpocząć</span> i <span style="color: red;">zakończyć</span> zmianę możemy tylko i wyłącznie wtedy, gdy zalogujemy się za pomocą czytnika.</p>
        <br>
        <video width="300" height="580" controls loop autoplay>
  <source src="/images/22.mp4" type="video/mp4">
  Twoja przeglądarka nie obsługuje odtwarzacza video.
</video>
<br>
<br>
<br>
<p> <span style="color: green;">Rozpocząć</span> zmianę możemy 15min przed ustaloną godziną rozpoczęcia zmiany, która znajduje się w grafiku.</p>
<br>
        <img src="rozpo.png" alt="rfidon" style="width: 300px;">

        <p>Klikamy 'Tak', aby rozpocząć zmianę.</p>
        <br>
        <br>
        <br>
        <img src="zako.png" alt="rfidon" style="width: 300px;">

        <p>Po zakończeniu zmiany również musimy się zalogować przez czytnik i klikając 'Tak' kończymy zmianę.</p>
        <br>        <br>
        <br>
        <p>W opcji menu 'Godziny pracy' możemy przeglądać nasze zmiany, ilości godzin, sumę godzin z danego miesiąca oraz przeglądać poprzednie miesiące.</p>
        <p> Dodatkowo dla pracowników
            restauracji, wyświetla się dodatkowa informacja Napiwki. Widzimy jaką kwotę napiwku posiadamy z danego dnia oraz sume z całego mięsiąca.
        </p>
        <br>
        <img src="hours.png" alt="rfidon" style="width: 350px;">
        <br>
        <br>
        <br>
        <h3>2. Panel użytkownika</h3>
        <p>W panelu użytkownika widzimy informacje takie jak: ilość godzin aktualnego miesiąca, najbliższe dni pracy oraz informacje od administratora.</p>
        <br>
        <img src="panel.png" alt="rfidon" style="width: 350px;">
        <br>
        <br>
        <br>
        <h3>3. Grafik</h3>
        <p>W opcjach menu mamy dwie zakładki dotyczące grafiku. 'Mój grafik' pokazuje nam indywidaulny grafik dotyczący naszej persony.</p>
        <p>'Pełny grafik' pokazuje nam grafik dla naszej kategorii, np. Kelnerzy widzą pełny grafik dla kategorii Kelner itd.</p>
        <p>Oba grafiki możemy pobrać do galerii jako IMG na nasze urządzenie.</p>
        <br>
        <img src="grafik.png" alt="rfidon" style="width: 150px;">
        <br>
        <br>
        <br>
        <h3>4. Checklist</h3>
        <p>Bez istotnych zmian :)</p>
        <br>
        <br>
        <h3>5. Wiadomości</h3>
        <p>Możemy zostawić wiadomość dla naszej kategorii, a pozostali uczestnicy czatu, otrzymają powiadomienie, gdy zalogują się do aplikacji.</p>
        <br>
        <img src="mess.png" alt="rfidon" style="width: 350px;">
        <br>
        <br>
        <br>
        <p>Wysłane wiadomości przez nas możemy usuwać.</p>
        <br>
        <img src="mess2.png" alt="rfidon" style="width: 250px;">
        <br>
        <br>
        <br>
        <h3>6. Mój profil</h3>
        <p>W tej zakładce widzimy informacje na nasz temat oraz obszar zawierający dane o urlopach.</p>
        <p>Informacje na temat urlopu: ilość godzin urlopu do wykorzystania, ile wykorzystaliśmy oraz ile godzin urlopu nam jeszcze przysługuje.</p>
        <br>
        <img src="profil.png" alt="rfidon" style="width: 250px;">
        <br>
        <br>


        

    </div>
    



</body>
</html>