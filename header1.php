<div class="custom-header">
  <div class="header-container-rmaster">
    <div class="text-partition-cont">
      <!-- first content row -->
      <div class="content-row">
        <div class="timer-container">
          <span id="current-date">November 21, 2024</span>
          <span id="current-time">7:28:50 AM</span>
        </div>
        <div class="company-info">
                
                <span class="heading-english">Ameena Chilies</span>
                <span class="company-motto">The Best Spicy</span>
            </div>
      </div>
    </div>
  </div>
</div>
<style>
  .custom-header .header-container-rmaster {
    width: 100%;
    min-height: 180px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #2980b9;
    position: relative;
    background-image: url("sample-back.png");
    background-size: contain;
  }

  .custom-header #current-date {
    font-size: 1.1em;
    color: white;
    font-family: "Poppins", serif;
  }

  .custom-header #current-time {
    font-size: 1.3em;
    color: white;
    font-family: "Poppins", serif;
    letter-spacing: 3px;
  }

  .custom-header .text-partition-cont {
    height: 100%;
    width: 77%;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
  }

  .custom-header .content-row {
    width: 100%;
    display: flex;
    justify-content: flex-start;
    align-items: center;
  }

  .custom-header .first-row {
    margin-bottom: 50px;
  }

  .custom-header .company-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    margin-left: 300px;
  }

  .custom-header .heading-english {
    font-family: 'Noto Serif', serif;
    font-size: 1.5em;
    margin-top: 6px;
    letter-spacing: 3px;
    font-weight: 500;
  }

  .custom-header .heading-sinhala {
    font-family: 'FM Bindumathi', serif;
    font-size: 2.5em;
    letter-spacing: 3px;
    font-weight: 600;
  }

  .custom-header .company-motto {
    font-family: "Yaldevi", serif;
    font-size: 1em;
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