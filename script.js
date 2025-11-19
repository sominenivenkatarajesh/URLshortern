function shortenURL() {
  const longURL = document.getElementById("longURL").value;
  const customAlias = document.getElementById("customAlias").value;

  if (!longURL) {
    document.getElementById("output").innerHTML = "❌ Please enter a valid URL!";
    return;
  }

  const shortAlias = customAlias || Math.random().toString(36).substring(2, 8);
  const shortURL = "myurl.com/" + shortAlias;

  document.getElementById("output").innerHTML = `✅ Short URL created: <a href="${longURL}" target="_blank">${shortURL}</a>`;
}