const video = document.getElementById("camera");
const scanButton = document.getElementById("scan-button");
const readButton = document.getElementById("read-button");
const resultDiv = document.getElementById("result");

let quaggaReady = false; // Track if Quagga is initialized

// Ensure camera access before initializing Quagga
if (video) {
  navigator.mediaDevices
    .getUserMedia({ video: { facingMode: "environment" } })
    .then((stream) => {
      video.srcObject = stream;
      video.play();
      initQuagga();
    })
    .catch((error) => {
      console.error("Camera access error:", error);
      resultDiv.innerHTML = `Camera access error: ${error.message}`;
    });
}

// Function to initialize Quagga
function initQuagga() {
  if (quaggaReady) return; // Prevent multiple initializations

  Quagga.init(
    {
      inputStream: {
        name: "Live",
        type: "LiveStream",
        target: video,
        constraints: {
          width: { ideal: 1280 }, // Wider
          height: { ideal: 720 }, // Shorter
          facingMode: "environment",
          aspectRatio: { ideal: 16 / 9 }, // Ensures landscape mode
        },
      },
      locator: {
        patchSize: "medium", // Scan area size
        halfSample: true,
      },
      area: {
        // Set scan rectangle
        top: "20%", // Adjust vertical position
        right: "10%",
        left: "10%",
        bottom: "20%",
      },
      decoder: {
        readers: ["ean_reader"],
      },
    },
    (err) => {
      if (err) {
        console.error("Error initializing Quagga:", err);
        resultDiv.innerHTML = `Error initializing scanner: ${err.message}`;
        return;
      }
      console.log("Quagga initialized successfully");
      quaggaReady = true;
      Quagga.onDetected(onBarcodeDetected);
      startScanning();
    }
  );
}

// Function to start scanning
function startScanning() {
  if (quaggaReady) {
    video.style.display = "block";
    scanButton.style.display = "none";
    readButton.style.display = "none";
    resultDiv.innerHTML = "Scanning...";
    Quagga.start();
  } else {
    console.error("Quagga is not initialized yet.");
  }
}

// Function to stop scanning
function stopScanning() {
  video.style.display = "none";
  scanButton.style.display = "block";
  Quagga.stop();
  quaggaReady = false; // Mark as not ready so it can be restarted
}

// Handle barcode detection
function onBarcodeDetected(data) {
  if (data && data.codeResult && data.codeResult.code) {
    stopScanning();
    const isbn = data.codeResult.code;
    resultDiv.innerHTML = `Scanned ISBN: ${isbn}`;
    fetchBookDetails(isbn);
  } else {
    console.warn("Invalid barcode data detected:", data);
  }
}

// Start scanning when button is clicked
if (scanButton) {
  scanButton.addEventListener("click", () => {
    if (!quaggaReady) {
      Quagga.stop(); // Ensure any previous instance is stopped
      initQuagga(); // Reinitialize if needed
    } else {
      startScanning();
    }
  });
}

// Send book details to the server
if (readButton) {
  readButton.addEventListener("click", () => {
    recordDetails("csv");
    recordDetails("task");
  });
}

// This function sends the book details to a PHP script for processing
function recordDetails(call) {
  const title = document
    .getElementById("book-title")
    .parentNode.textContent.replace("Title: ", "")
    .trim();
  console.log(title);
  const authors = document
    .getElementById("authors")
    .parentNode.textContent.replace("Author(s): ", "")
    .trim();
  console.log(authors);
  const subject = document
    .getElementById("subject")
    .parentNode.textContent.replace("Subject: ", "")
    .trim();
  console.log(subject);
  const url = document
    .querySelector("button[onclick]")
    .getAttribute("onclick")
    .match(/window\.open\('(.*?)'/)[1];
  console.log(url);

  const data = { title, authors, subject, url };
  readButton.style.display = "none";

  fetch("record_" + call + ".php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        resultDiv.innerHTML = "Details successfully recorded!";
      } else {
        resultDiv.innerHTML = `Error recording details: ${result.error}`;
      }
    })
    .catch((error) => {
      resultDiv.innerHTML = `Error: ${error.message}`;
    });
}

// Function to fetch book details using ISBN
function fetchBookDetails(isbn) {
  fetch(`fetch_book.php?isbn=${isbn}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        readButton.style.display = "none";
        resultDiv.innerHTML = `Error: ${data.error} - ${isbn}`;
      } else {
        resultDiv.innerHTML = `
                <h2>Book Details</h2>
                <p><strong id="book-title">Title:</strong> ${data.title}</p>
                <p><strong id="authors">Author(s):</strong> ${data.authors.join(
                  ", "
                )}</p>
                <p><strong id="subject">Subject:</strong> ${data.subject}</p>
                <p><strong>Publisher:</strong> ${data.publisher}</p>
                <p><strong>Published Date:</strong> ${data.publish_date}</p>
                <p><strong>ISBN:</strong> ${data.isbn}</p>
                <button onclick="window.open('${
                  data.url
                }', '_blank')"><strong>Open Library Link</strong></button>
            `;
        readButton.style.display = "block";
      }
    })
    .catch((error) => {
      readButton.style.display = "none";
      resultDiv.innerHTML = `Error fetching book details: ${error.message}`;
    });
}

$(document).ready(function () {
  const table = $("#results-table").DataTable();

  $("#book-search-form").on("submit", function (e) {
    e.preventDefault();
    const query = $("#book-title").val();

    $.ajax({
      url: `https://openlibrary.org/search.json`,
      method: "GET",
      data: { title: query },
      success: function (data) {
        table.clear();
        data.docs.forEach((book) => {
          const title = book.title || "Unknown Title";
          const author = book.author_name
            ? book.author_name.join(", ")
            : "Unknown Author";
          const link = `https://openlibrary.org${book.key}`;
          table.row.add([
            `<a href="${link}" target="_blank">${title}</a>`,
            author,
          ]);
        });
        table.draw();
      },
      error: function () {
        alert("An error occurred while fetching data.");
      },
    });
  });
});
