/*const signInBtn = document.getElementById("signIn");
const signUpBtn = document.getElementById("signUp");
const firstform = document.getElementById("form1");
const secondform = document.getElementById("form2");
const container = document.querySelector(".container");

signInBtn.addEventListener("click", () => {
    container.classList.remove("right-panel-active");
});

signUpBtn.addEventListener("click", () => {
    container.classList.add("right-panel-active");
});

firstform.addEventListener("submit", (e) => {
    e.preventDefault();
    // Handle sign-up form submission with AJAX
    const formData = new FormData(firstform);
    fetch(firstform.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        // Handle response from the server
    })
    .catch(error => console.error('Error:', error));
});

secondform.addEventListener("submit", (e) => {
    e.preventDefault();
    // Handle sign-in form submission with AJAX
    const formData = new FormData(secondform);
    fetch(secondform.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        // Handle response from the server
    })
    .catch(error => console.error('Error:', error));
});*/
const signInBtn = document.getElementById("signIn");
const signUpBtn = document.getElementById("signUp");
const firstform = document.getElementById("form1");
const secondform = document.getElementById("form2");
const container = document.querySelector(".container");

signInBtn.addEventListener("click", () => {
    container.classList.remove("right-panel-active");
});

signUpBtn.addEventListener("click", () => {
    container.classList.add("right-panel-active");
});