
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"  href="../../assets/css/style.css">
    <script defer src="../../assets/js/main1.js"></script>
    <title>authentication page</title>
</head>
<body>
<h2 class="h1">
<!--<img src="../../assets/images/logo(1).png" alt="logo" class="logo">--> ScheduleHub
    </h2>
    <div class="container right-panel-active">
    <!-- Sign Up -->
    <div class="container__form container--signup">
    <form action="https://localhost/schedulehub/controllers/process_signup.php" method="POST" class="form" id="form1">
        <h2 class="form__title">Sign Up</h2><br>
        <input type="text" name="name" placeholder="User" class="input" required/>
        <input type="email" name="email" placeholder="me@email.com" class="input" required/>
        <input type="password" name="password" placeholder="password" class="input" required/>
            <div class="form-group">
                <select name="role" class="input" required>
                <option value="1">Student</option>
                <option value="2">Instructor</option>
                </select>
            <button type="submit" class="btn">Sign Up</button>
            </div>
    </form>
    </div>

    <!-- Sign In -->
    <div class="container__form container--signin">


    <form action="../../controllers/process_login.php" method="POST" class="form" id="form2">
                <h2 class="form__title">Sign In</h2>
        <input type="email" name="email" placeholder="me@email.com" class="input" />
        <input type="password" name="password" placeholder="password" class="input" />
        <a href="#" class="link">Forgot your password?</a>
        <button class="btn">Sign In</button>
    </form>
    </div>

    <!-- Overlay -->
    <div class="container__overlay">
    <div class="overlay">
        <div class="overlay__panel overlay--left">
        <button class="btn" id="signIn">Sign In</button>
        </div>
        <div class="overlay__panel overlay--right">
        <button class="btn" id="signUp">Sign Up</button>
        </div>
    </div>
    </div>
    </div>
    
</body>
</html>
