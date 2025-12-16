  <!-- Main Header -->
  <header class="main-header header-style-one">
      <!-- Header Lower -->
      <div class="header-lower">
          <div class="auto-container navbar-custom-container">
              <div class="inner-container">
                  <div class="d-flex justify-content-between align-items-center flex-wrap">

                      <div class="logo-box">
                          <div class="logo"><a href="/"><img src="assets/images/logo.svg" alt="Metafonics Logo" title="Metafonics Logo"></a></div>
                      </div>

                      <div class="nav-outer d-flex flex-wrap">
                          <!-- Main Menu -->
                          <nav class="main-menu navbar-expand-md">
                              <div class="navbar-header">
                                  <!-- Toggle Button -->
                                  <button class="navbar-toggler" type="button" data-toggle="collapse"
                                      data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                      aria-expanded="false" aria-label="Toggle navigation">
                                      <span class="icon-bar"></span>
                                      <span class="icon-bar"></span>
                                      <span class="icon-bar"></span>
                                  </button>
                              </div>

                              <div class="navbar-collapse collapse clearfix" id="navbarSupportedContent">
                                  <ul class="navigation clearfix">
                                      <li class="<?= ($request == "" || $request == "/") ? "active" : "" ?>">
                                          <a href="/">Anasayfa</a>
                                      </li>

                                      <li class="<?= ($request == "hakkimizda") ? "active" : "" ?>">
                                          <a href="/hakkimizda">Hakkımızda</a>
                                      </li>

                                      <li class="dropdown <?= in_array($request, [
                                                                "sektorel-asistanlar",
                                                                "sektorel-asistanlar/okul-asistani",
                                                                "sektorel-asistanlar/egitim-ve-kurs-asistani",
                                                                "sektorel-asistanlar/egitim-ve-kurs-asistani",
                                                                "sektorel-asistanlar/eczane-asistani",
                                                                "sektorel-asistanlar/turizm-ve-seyahat-acentasi-asistani",
                                                                "sektorel-asistanlar/otomotiv-asistani",
                                                                "sektorel-asistanlar/aile-saglik-merkezi-asistani",
                                                                "sektorel-asistanlar/emlak-asistani",
                                                                "sektorel-asistanlar/surucu-kursu-asistani",
                                                                "sektorel-asistanlar/guzellik-merkezi-asistani",
                                                                "sektorel-asistanlar/kombi-servis-asistani",
                                                                "sektorel-asistanlar/klima-servis-asistani",
                                                                "sektorel-asistanlar/su-aritma-asistani",
                                                                "sektorel-asistanlar/cagri-merkezi-asistani",
                                                            ]) ? "active" : "" ?>">
                                          <a href="#">Sektörel Asistanlar</a>
                                          <ul>
                                              <li><a href="/sektorel-asistanlar">Tüm Sektörler</a></li>
                                              <li><a href="/sektorel-asistanlar/okul-asistani">Okul</a></li>
                                              <li><a href="/sektorel-asistanlar/egitim-ve-kurs-asistani">Eğitim ve Kurs</a></li>
                                              <li><a href="/sektorel-asistanlar/eczane-asistani">Eczane</a></li>
                                              <li><a href="/sektorel-asistanlar/turizm-ve-seyahat-acentasi-asistani">Turizm ve Seyahat Acentası</a></li>
                                              <li><a href="/sektorel-asistanlar/otomotiv-asistani">Otomotiv</a></li>
                                              <li><a href="/sektorel-asistanlar/aile-saglik-merkezi-asistani">Aile Sağlık Merkezi</a></li>
                                              <li><a href="/sektorel-asistanlar/emlak-asistani">Emlak</a></li>
                                              <li><a href="/sektorel-asistanlar/surucu-kursu-asistani">Sürücü Kursu</a></li>
                                              <li><a href="/sektorel-asistanlar/guzellik-merkezi-asistani">Güzellik Merkezi</a></li>
                                              <li><a href="/sektorel-asistanlar/kombi-servis-asistani">Kombi Servis</a></li>
                                              <li><a href="/sektorel-asistanlar/klima-servis-asistani">Klima Servis</a></li>
                                              <li><a href="/sektorel-asistanlar/su-aritma-asistani">Su Arıtma</a></li>
                                              <li><a href="/sektorel-asistanlar/cagri-merkezi-asistani">Çağrı Merkezi Asistanı</a></li>
                                          </ul>
                                      </li>

                                      <li class="dropdown <?= in_array($request, [
                                                                "satis-pazarlama-asistani",
                                                                "tahsilat-asistani",
                                                                "insan-kaynaklari-asistani",
                                                                "tedarik-asistani",
                                                            ]) ? "active" : "" ?>">
                                          <a href="#">Fonksiyonel Asistanlar</a>
                                          <ul>
                                              <li><a href="/satis-pazarlama-asistani">Satış / Pazarlama</a></li>
                                              <li><a href="/tahsilat-asistani">Tahsilat</a></li>
                                              <li><a href="/insan-kaynaklari-asistani">İnsan Kaynakları</a></li>
                                              <li><a href="/tedarik-asistani">Tedarik</a></li>
                                          </ul>
                                      </li>

                                      <li id="sss-menu-item">
                                          <a href="#sik-sorulan-sorular">S.S.S</a>
                                      </li>

                                      <li class="<?= ($request == "referanslarimiz") ? "active" : "" ?>">
                                          <a href="/referanslarimiz">Referanslarımız</a>
                                      </li>

                                      <li class="<?= ($request == "iletisim") ? "active" : "" ?>">
                                          <a href="/iletisim">İletişim</a>
                                      </li>
                                  </ul>
                              </div>
                          </nav>
                      </div>

                      <!-- Main Menu End-->
                      <div class="outer-box d-flex align-items-center flex-wrap">

                          <!-- Language DropDown -->
                          <!-- <div class="language-dropdown">
                              <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                  <span class="flag"><img src="assets/images/icons/turkish.png" alt="" /></span> <span class="fa-solid fa-angle-down fa-fw"></span>
                              </button>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                  <li><a class="dropdown-item" href="#"><span class="flag"><img src="assets/images/icons/turkish.png" alt="" /></span> Türkçe</a></li>
                                  <li><a class="dropdown-item" href="#"><span class="flag"><img src="assets/images/icons/arabic.png" alt="" /></span> Arbic</a></li>
                                  <li><a class="dropdown-item" href="#"><span class="flag"><img src="assets/images/icons/germany.png" alt="" /></span> German</a></li>
                                  <li><a class="dropdown-item" href="#"><span class="flag"><img src="assets/images/icons/france.png" alt="" /></span> French</a></li>
                              </ul>
                          </div> -->

                          <!-- Button Box -->
                          <div class="main-header_buttons">
                              <!-- <a href="#" class="template-btn btn-style-two">
										<span class="btn-wrap">
											<span class="text-one">Login</span>
											<span class="text-two">Login</span>
										</span>
									</a> -->
                              <a href="/iletisim" class="template-btn btn-style-one">
                                  <span class="btn-wrap">
                                      <span class="text-one">Şimdi Katılın</span>
                                      <span class="text-two">Şimdi Katılın</span>
                                  </span>
                              </a>
                          </div>

                          <!-- Mobile Navigation Toggler -->
                          <div class="mobile-nav-toggler">
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-menu-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                  <path d="M4 6l16 0" />
                                  <path d="M4 12l16 0" />
                                  <path d="M4 18l16 0" />
                              </svg>
                          </div>

                      </div>

                  </div>
              </div>
          </div>
      </div>
      <!--End Header Lower-->

      <!-- Mobile Menu  -->
      <div class="mobile-menu">
          <div class="menu-backdrop"></div>
          <div class="close-btn"><span class="icon fa-solid fa-xmark fa-fw"></span></div>

          <nav class="menu-box">
              <div class="nav-logo"><a href="index.html">
                      <img src="assets/images/mobile-logo.svg" alt="Metafonics Logo" title="Metafonics Logo">
                  </a></div>
              <div class="menu-outer"><!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header--></div>
          </nav>
      </div>
      <!-- End Mobile Menu -->
  </header>
  <!-- End Main Header -->


  <script>
      document.addEventListener("DOMContentLoaded", function() {
          const menuItems = document.querySelectorAll('.navigation li');
          const sssMenuItem = document.querySelector('#sss-menu-item');
          const sssSection = document.querySelector('#sik-sorulan-sorular');

          window.addEventListener('scroll', function() {
              if (!sssSection) return; // Element yoksa çık

              const scrollPos = window.scrollY || document.documentElement.scrollTop;
              const sectionTop = sssSection.offsetTop;
              const sectionBottom = sectionTop + sssSection.offsetHeight;

              // Menüleri sıfırla
              menuItems.forEach(li => li.classList.remove('active'));

              // Eğer scroll, section’ın içindeyse active ekle
              if (scrollPos + 150 >= sectionTop && scrollPos < sectionBottom) {
                  sssMenuItem.classList.add('active');
              }
          });
      });
  </script>