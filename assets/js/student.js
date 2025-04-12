document.addEventListener("DOMContentLoaded", function () {
  const profileButton = document.getElementById("profileButton");
  const dropdownMenu = document.getElementById("profileDropdown");

  profileButton.addEventListener("click", function (event) {
    event.stopPropagation();
    dropdownMenu.classList.toggle("active");
  });

  document.addEventListener("click", function (event) {
    if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
      dropdownMenu.classList.remove("active");
    }
  });
});

function openDiagnoseNav() {
  document.getElementById("diagnosisNav").classList.add("open");
}

function closeDiagnoseNav() {
  document.getElementById("diagnosisNav").classList.remove("open");
}

async function getDiagnosis() {
  const symptom = document.getElementById("searchInput").value.toLowerCase().trim();
  const age = document.getElementById("ageInput").value;
  const gender = document.getElementById("botGenderInput").value;
  const weight = document.getElementById("weightInput").value;
  const resultDiv = document.getElementById("diagnosisResult");
  const solutionBtn = document.getElementById("solutionBtn");
  const solutionText = document.getElementById("solutionText");

  if (!symptom || !age || !gender || !weight) {
    resultDiv.innerHTML = "Please enter Symptom, Age, Gender, and Weight.";
    return;
  }

  try {
    const response = await fetch(`fetch_diseases.php?query=${encodeURIComponent(symptom)}&age=${age}&weight=${weight}`);
    const data = await response.json();

    if (!data || data.length === 0) {
      resultDiv.innerHTML = "No matching medical conditions found.";
      solutionBtn.style.display = "none";
      solutionText.innerHTML = "";
      return;
    }

    const disease = data[0]; // show top match
    resultDiv.innerHTML = `
  <strong>Possible Condition:</strong> ${disease.disease}<br>
  <strong>Recommended Doctor:</strong> ${disease.recommended_specialist}<br>
  <strong>Gender:</strong> ${disease.gender}<br>
  <strong>Age Range:</strong> ${disease.age} years<br>
  <strong>Weight Range:</strong> ${disease.weight} kg<br>
  <strong>Symptoms:</strong> ${disease.symptoms}
`;

    solutionBtn.style.display = "inline-block";
    solutionBtn.setAttribute("data-firstaid", disease.first_aid_tips);
    solutionBtn.setAttribute("data-link", disease.tutorial_link);
    solutionText.innerHTML = "";

  } catch (error) {
    resultDiv.innerHTML = "Error fetching diagnosis data.";
    console.error(error);
  }
}


function showSolution() {
  const tips = document.getElementById("solutionBtn").getAttribute("data-firstaid");
  const link = document.getElementById("solutionBtn").getAttribute("data-link");
  const solutionText = document.getElementById("solutionText");

  solutionText.innerHTML = `
<strong>First Aid Tips:</strong><br>${tips.replace(/\n/g, "<br>")}<br><br>
<a href="${link}" target="_blank" class="btn btn-primary botButtonProperty">Watch Tutorial</a>
`;
  solutionText.style.display = "block";
}


async function showSuggestions() {
  const input = document.getElementById("searchInput").value.trim().toLowerCase();
  const suggestionsBox = document.getElementById("suggestionsBox");

  if (input.length === 0) {
    suggestionsBox.style.display = "none";
    return;
  }

  try {
    const response = await fetch(`fetch_diseases.php?query=${encodeURIComponent(input)}`);
    const data = await response.json();

    if (!data || data.length === 0) {
      suggestionsBox.style.display = "none";
      return;
    }

    suggestionsBox.innerHTML = data.map(item => `
  <div onclick="selectSuggestion('${item.symptoms.replace(/'/g, "\\'")}')">
    <strong>${item.disease}</strong><br>
    <small>${item.symptoms}</small>
  </div>
`).join("");

    suggestionsBox.style.display = "block";

  } catch (error) {
    console.error("Error fetching suggestions:", error);
    suggestionsBox.style.display = "none";
  }
}

function selectSuggestion(symptom) {
  document.getElementById("searchInput").value = symptom;
  document.getElementById("suggestionsBox").style.display = "none";
}