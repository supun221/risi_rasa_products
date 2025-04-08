<div class="custom-header">
  <div class="header-container">
    <div class="center-wrapper">
      <div class="timer-display">
        <div class="date-time-container">
          <div class="date-display" id="current-date">November 21, 2024</div>
          <div class="time-display" id="current-time">7:28:50 AM</div>
        </div>
      </div>
      <div class="logo-brand-wrapper">
        <div class="logo-section">
          <div class="logo-circle">R</div>
        </div>
        <div class="brand-info">
          <div class="brand-name">
            <h1 class="main-text">RISI RASA</h1>
            <h2 class="sub-text">PRODUCTS</h2>
          </div>
          <div class="brand-tagline">Delicious Treats Since 1995</div>
        </div>
      </div>
    </div>
    <div class="contact-section">
      <div class="contact-title">Contact Us</div>
      <div class="contact-details">
        <div class="contact-item">
          <span class="contact-icon">📞</span>
          <span class="contact-text">075 2155499 / 075 7204220</span>
        </div>
        <!-- <div class="contact-item">
          <span class="contact-icon">✉️</span>
          <span class="contact-text">info@risirasa.lk</span>
        </div> -->
        <div class="contact-item">
          <span class="contact-icon">🏢</span>
          <span class="contact-text">No 548/1 ,Near the Hospital,Morayaya,Minipe</span>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  :root {
    --header-padding: 12px;
    --header-gap: 2px;
    --logo-size: 55px;
    --brand-margin: 0;
    --time-margin: 5px;
    --contact-margin: 5px;
    --primary-color: #d44949;
    --secondary-color: #e47f7f;
    --bg-color: #fcf5f5;
    --text-color: #483535;
    --left-position: 20%; 
  }
  
  .custom-header {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
  }
  
  .header-container {
    width: 100%;
    background-color: var(--bg-color);
    background-image: linear-gradient(to right, var(--bg-color), #fff0f0);
    color: var(--text-color);
    border-radius: 8px;
    padding: var(--header-padding);
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start; 
    align-items: center;
    position: relative;
    overflow: hidden;
    border: 1px solid #e9a8a8;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    box-sizing: border-box;
    gap: var(--header-gap); 
    padding-left: var(--left-position); 
  }
  
  .center-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-start; 
    flex-wrap: wrap;
    margin: 0;
    margin-right: auto; 
    text-align: center;
    gap: var(--header-gap);
  }
  
  .logo-brand-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    margin-left: var(--brand-margin);
  }
  
  .logo-section {
    position: relative;
    margin-right: 10px;
    flex: 0 0 auto;
    margin-left: 0; 
  }
  
  .logo-circle {
    width: var(--logo-size);
    height: var(--logo-size);
    background-color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    border: 2px solid #ffffff;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  }
  
  .brand-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    position: relative;
    padding-left: 10px;
    border-left: 3px solid var(--primary-color);
    flex: 1 1 auto;
    min-width: 0;
    margin-right: 5px;
    margin-left: 0; 
  }
  
  .brand-name {
    margin-bottom: 3px;
   
  }
  
  .main-text {
    color: var(--primary-color);
    font-size: 34px;
    margin: 0;
    font-weight: 700;
    letter-spacing: 1px;
  }
  
  .sub-text {
    color: var(--secondary-color);
    font-size: 22px;
    margin: 0;
    font-weight: 600;
  }
  
  .brand-tagline {
    color: #777;
    font-style: italic;
    margin-top: 3px;
    font-size: 13px;
  }
  
  .timer-display {
    background-color: #f8f8f8;
    border-radius: 6px;
    padding: 8px 12px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin: 0 var(--time-margin); /* Reduced from 10px */
    flex: 0 0 auto;
    text-align: center;
    min-width: 180px;
  }
  
  .date-time-container {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .date-display {
    font-size: 15px;
    color: #666;
    margin-bottom: 5px;
  }
  
  .time-display {
    font-size: 22px;
    font-weight: bold;
    color: var(--primary-color);
  }
  
  .contact-section {
    background-color: #f8f8f8;
    border-radius: 6px;
    padding: 10px;
    margin-left: auto; 
    margin-right: 10px;
    flex: 0 0 auto;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  
  .contact-title {
    font-weight: bold;
    font-size: 15px;
    margin-bottom: 8px;
    text-align: center;
    background-color: var(--primary-color);
    color: #ffffff;
    padding: 4px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  
  .contact-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  
  .contact-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }
  
  .contact-icon {
    font-size: 13px;
    background-color: var(--primary-color);
    color: #ffffff;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
  }
  
  .contact-text {
    font-size: 12px;
    color: #555;
  }
  
  @media (max-width: 1200px) {
    :root {
      --time-margin: 2px;
      --contact-margin: 2px;
      --left-position: 2%; /* Reduced from 8% */
    }
    
    .timer-display, .contact-section {
      margin: 2px;
    }
    
    .brand-info {
      padding-left: 8px;
    }
  }
  
  @media (max-width: 992px) {
    :root {
      --header-padding: 10px;
      --header-gap: 8px;
      --left-position: 1%; /* Reduced from 5% */
    }
    
    .header-container {
      flex-wrap: wrap;
      padding: 10px;
      gap: 8px;
      padding-left: 0; /* Reset in smaller screens */
      justify-content: center;
    }
    
    .brand-info {
      flex: 1 1 45%;
      margin-right: 0;
    }
    
    .timer-display {
      flex: 0 0 auto;
      min-width: 160px;
      margin: 0 2px; /* Reduced from 5px */
    }
    
    .contact-section {
      flex: 1 1 100%;
      margin: 5px 0 0 0;
      width: 100%;
      order: 3;
    }
    
    .center-wrapper {
      flex: 1 1 auto;
      width: 100%;
      justify-content: center;
    }
  }
  
  @media (max-width: 768px) {
    :root {
      --logo-size: 50px;
      --left-position: 0%; /* Remove shift on mobile */
    }
    
    .header-container {
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 10px;
      padding-left: 10px;
    }
    
    .logo-brand-wrapper {
      flex-direction: column;
      align-items: center;
      order: 2;
    }
    
    .brand-info {
      padding-left: 0;
      border-left: none;
      border-bottom: 3px solid #d44949;
      padding-bottom: 15px;
      align-items: center;
      width: 100%;
      flex: 1 1 100%;
    }
    
    .logo-section {
      position: relative;
      top: 0;
      right: 0;
      margin-bottom: 10px;
      margin-right: 0;
    }
    
    .timer-display {
      width: 100%;
      margin: 0 0 10px 0;
      order: 1;
    }
    
    .contact-section {
      width: 100%;
      margin: 10px 0 0 0;
      order: 3;
    }
    
    .center-wrapper {
      flex-direction: column;
      width: 100%;
      order: 1;
    }
  }
  
  @media (max-width: 480px) {
    :root {
      --logo-size: 45px;
    }
    
    .main-text {
      font-size: 30px;
    }
    
    .sub-text {
      font-size: 20px;
    }
    
    .contact-details {
      font-size: 12px;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    updateDateTime();
    setInterval(updateDateTime, 1000);
  });

  function updateDateTime() {
    const now = new Date();
    const timeElement = document.getElementById('current-time');
    timeElement.textContent = now.toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    });

    const dateElement = document.getElementById('current-date');
    dateElement.textContent = now.toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    });
  }
</script>