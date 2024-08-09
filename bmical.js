document.getElementById('bmiForm').addEventListener('submit', function(event) {
    event.preventDefault();

    // Get user input
    const weight = parseFloat(document.getElementById('weight').value);
    const height = parseFloat(document.getElementById('height').value) / 100; // Convert cm to meters

    // Check if inputs are valid numbers
    if (isNaN(weight) || isNaN(height) || weight <= 0 || height <= 0) {
        document.getElementById('bmiResult').innerHTML = '<h2>Please enter valid weight and height.</h2>';
        return;
    }

    // Calculate BMI
    const bmi = weight / (height * height);

    // Display result
    const resultElement = document.getElementById('bmiResult');
    resultElement.innerHTML = `<h2>Your BMI is ${bmi.toFixed(2)}</h2>`;
});
