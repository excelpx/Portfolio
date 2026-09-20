<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Portfolio-Excel</title>
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
  <link href="{{ asset('img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('css/main.css') }}" rel="stylesheet">
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="{{ url('/') }}" class="logo d-flex align-items-center">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="{{ asset('img/logo.png') }}" alt=""> -->
        <h1 class="sitename">Portfolio</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home<br></a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="#portfolio">Portfolio</a></li>
          <li><a href="#tools-technologies">Tools &amp; Technologies</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

      @php
        $heroBackground = trim((string) ($hero['background_image'] ?? ''));
        $heroBackgroundUrl = filter_var($heroBackground, FILTER_VALIDATE_URL)
          ? $heroBackground
          : asset($heroBackground !== '' ? $heroBackground : 'img/hero-img.jpg');
      @endphp
      <img src="{{ $heroBackgroundUrl }}" alt="" data-aos="fade-in">

      <div class="container d-flex flex-column align-items-center justify-content-center text-center" data-aos="fade-up" data-aos-delay="100">
        <h2>{{ $profile['name'] ?? 'Morgan Freeman' }}</h2>
        <p><span class="typed" data-typed-items="{{ $profile['profession'] ?? 'Designer, Developer, Freelancer, Photographer' }}"></span></p>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">
          <div class="col-md-6">

            <div class="row justify-content-between gy-4">
              <div class="col-lg-5">
                <img
                  src="{{ !empty($profile['image']) && filter_var($profile['image'], FILTER_VALIDATE_URL) ? $profile['image'] : asset($profile['image'] ?? 'img/profile-img.jpg') }}"
                  class="img-fluid"
                  alt="{{ $profile['name'] ?? 'Profile' }}">
              </div>
              <div class="col-lg-7 about-info">
                <p><strong>Name: </strong> <span>{{ $profile['name'] ?? 'Morgan Freeman' }}</span></p>
                <p><strong>Profile: </strong> <span>{{ $profile['profession'] ?? 'full stack developer' }}</span></p>
                <p><strong>Email: </strong> <span>{{ $profile['email'] ?? 'contact@example.com' }}</span></p>
                <p><strong>Phone: </strong> <span>{{ $profile['phone'] ?? '(617) 557-0089' }}</span></p>
              </div>
            </div>

            <div class="skills-content skills-animation">

              <h5>Skills</h5>

              @foreach($skills as $skill)
                @php
                  $percentage = is_numeric($skill['percentage'] ?? null)
                    ? max(0, min(100, (int) $skill['percentage']))
                    : 0;
                @endphp

                <div class="progress">
                  <span class="skill"><span>{{ $skill['name'] ?? '' }}</span> <i class="val">{{ $percentage }}%</i></span>
                  <div class="progress-bar-wrap">
                    <div class="progress-bar" role="progressbar" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div><!-- End Skills Item -->
              @endforeach

            </div>
          </div>

          <div class="col-md-6">
            <div class="about-me">
              <h4>About me</h4>
              <p>{!! nl2br(e($profile['description'] ?? 'Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem.')) !!}</p>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Resume Section -->
    <section id="resume" class="resume section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>{{ $resume['title'] ?? 'Resume' }}</h2>
        <p>{{ $resume['subtitle'] ?? 'Projek yang saya kerjakan & Kembangkan' }}</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row">

          @php
            $resumeSummary = is_array($resume['summary'] ?? null) ? $resume['summary'] : [];
            $resumeItems = is_array($resume['items'] ?? null) ? $resume['items'] : [];
            $educationItems = [];
            $experienceItems = [];
            foreach ($resumeItems as $resumeItem) {
              if (!is_array($resumeItem)) {
                continue;
              }
              if (($resumeItem['type'] ?? '') === 'experience') {
                $experienceItems[] = $resumeItem;
              } else {
                $educationItems[] = $resumeItem;
              }
            }
          @endphp

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <h3 class="resume-title">Sumary</h3>

            @if(!empty($resumeSummary))
              <div class="resume-item pb-0">
                <h4>{{ $resumeSummary['title'] ?? '' }}</h4>
                <p><em>{{ $resumeSummary['description'] ?? '' }}</em></p>
                <ul>
                  @foreach(['address', 'phone', 'email'] as $summaryField)
                    @if(!empty($resumeSummary[$summaryField]))
                      <li>{{ $resumeSummary[$summaryField] }}</li>
                    @endif
                  @endforeach
                </ul>
              </div>
            @endif

            <h3 class="resume-title">Education</h3>
            @foreach($educationItems as $resumeItem)
              <div class="resume-item">
                <h4>{{ $resumeItem['title'] ?? '' }}</h4>
                <h5>{{ $resumeItem['period'] ?? '' }}</h5>
                <p><em>{{ $resumeItem['organization'] ?? '' }}</em></p>
                @if(!empty($resumeItem['description']))
                  <p>{{ $resumeItem['description'] }}</p>
                @endif
              </div>
            @endforeach
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <h3 class="resume-title">Professional Experience</h3>
            @foreach($experienceItems as $resumeItem)
              <div class="resume-item">
                <h4>{{ $resumeItem['title'] ?? '' }}</h4>
                <h5>{{ $resumeItem['period'] ?? '' }}</h5>
                <p><em>{{ $resumeItem['organization'] ?? '' }}</em></p>
                @if(!empty($resumeItem['description']))
                  <p>{{ $resumeItem['description'] }}</p>
                @endif
                @if(is_array($resumeItem['details'] ?? null) && count($resumeItem['details']) > 0)
                  <ul>
                    @foreach($resumeItem['details'] as $detail)
                      @if(is_string($detail) && trim($detail) !== '')
                        <li>{{ $detail }}</li>
                      @endif
                    @endforeach
                  </ul>
                @endif
              </div>
            @endforeach
          </div>

        </div>

      </div>

    </section><!-- /Resume Section -->

    <!-- Services Section -->
    <section id="services" class="services section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>{{ $services['title'] ?? 'Services' }}</h2>
        <p>{{ $services['subtitle'] ?? 'Layanan yang saya tawarkan' }}</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4">

          @php
            $serviceItems = is_array($services['items'] ?? null) ? $services['items'] : [];
          @endphp
          @foreach($serviceItems as $index => $service)
            @if(is_array($service))
              @php
                $configuredIcon = trim((string) ($service['icon'] ?? ''));
                $serviceIcon = 'bi bi-activity';

                if (preg_match('/^<i\s+class=["\']([^"\']+)["\']\s*><\/i>$/i', $configuredIcon, $iconMatch)) {
                  $configuredIcon = trim($iconMatch[1]);
                }

                if (preg_match('/^(?:bi\s+bi-[a-z0-9-]+|bi-[a-z0-9-]+)$/i', $configuredIcon)) {
                  $serviceIcon = str_starts_with(strtolower($configuredIcon), 'bi ')
                    ? $configuredIcon
                    : 'bi ' . $configuredIcon;
                }
              @endphp
              <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ((intval($index) + 1) * 100) }}">
                <div class="service-item position-relative">
                  <div class="icon">
                    <i class="{{ $serviceIcon }}"></i>
                  </div>
                  <a href="#" class="stretched-link">
                    <h3>{{ $service['name'] ?? '' }}</h3>
                  </a>
                  <p>{{ $service['description'] ?? '' }}</p>
                </div>
              </div><!-- End Service Item -->
            @endif
          @endforeach

        </div>

      </div>

    </section><!-- /Services Section -->

    <!-- Stats Section -->
    <section id="stats" class="stats section accent-background">

      @php
        $statisticsSection = is_array($statistics['section'] ?? null) ? $statistics['section'] : [];
        $statisticsItems = [];
        if (is_array($statistics['items'] ?? null)) {
          foreach ($statistics['items'] as $statisticsItem) {
            if (is_array($statisticsItem)) {
              $statisticsItems[] = $statisticsItem;
            }
          }
        }
        usort($statisticsItems, static fn (array $left, array $right): int => ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0)));
        $statisticsBackground = trim((string) ($statisticsSection['background_image'] ?? 'img/stats-bg.jpg'));
        $statisticsBackgroundUrl = filter_var($statisticsBackground, FILTER_VALIDATE_URL)
          ? $statisticsBackground
          : asset($statisticsBackground !== '' ? $statisticsBackground : 'img/stats-bg.jpg');
      @endphp

      <img src="{{ $statisticsBackgroundUrl }}" alt="" data-aos="fade-in">

      <div class="container position-relative" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          @foreach($statisticsItems as $statistic)
            @if(is_array($statistic))
              @php
                $configuredStatisticIcon = trim((string) ($statistic['icon'] ?? ''));
                $statisticIcon = 'bi bi-bar-chart';

                if (preg_match('/^<i\s+class=["\']([^"\']+)["\']\s*><\/i>$/i', $configuredStatisticIcon, $iconMatch)) {
                  $configuredStatisticIcon = trim($iconMatch[1]);
                }

                if (preg_match('/^(?:bi\s+bi-[a-z0-9-]+|bi-[a-z0-9-]+)$/i', $configuredStatisticIcon)) {
                  $statisticIcon = str_starts_with(strtolower($configuredStatisticIcon), 'bi ')
                    ? $configuredStatisticIcon
                    : 'bi ' . $configuredStatisticIcon;
                }

                $statisticValue = max(0, (int) ($statistic['value'] ?? 0));
              @endphp
              <div class="col-lg-3 col-md-6">
                <div class="stats-item text-center w-100 h-100">
                  <i class="{{ $statisticIcon }}" aria-hidden="true"></i>
                  <span data-purecounter-start="0" data-purecounter-end="{{ $statisticValue }}" data-purecounter-duration="0" class="purecounter">{{ $statisticValue }}</span>
                  <p>{{ $statistic['label'] ?? '' }}</p>
                </div>
              </div><!-- End Stats Item -->
            @endif
          @endforeach

        </div>

      </div>

    </section><!-- /Stats Section -->

    <!-- Tools & Technologies Section -->
    @php
      $toolItems = [];
      if (is_array($toolsTechnologies ?? null)) {
        foreach ($toolsTechnologies as $tool) {
          if (is_array($tool) && (($tool['status'] ?? true) === true || ($tool['status'] ?? true) === 1 || ($tool['status'] ?? true) === '1' || strtolower((string) ($tool['status'] ?? '')) === 'aktif')) {
            $toolItems[] = $tool;
          }
        }
      }
      usort($toolItems, static fn (array $left, array $right): int => ((int) ($left['sort_order'] ?? 0)) <=> ((int) ($right['sort_order'] ?? 0)));
      $toolCount = count($toolItems);
    $toolsSwiperConfig = [
    'loop' => $toolCount > 2,
    'speed' => 10000,
    'slidesPerView' => 3,
    'centeredSlides' => true,
    'spaceBetween' => 0,
    'allowTouchMove' => true,

    'breakpoints' => [
        0 => [
            'slidesPerView' => 1.5,
            'spaceBetween' => 0,
        ],
        576 => [
            'slidesPerView' => 3,
            'spaceBetween' => 0,
        ],
        768 => [
            'slidesPerView' => 3,
            'spaceBetween' => 0,
        ],
        1200 => [
            'slidesPerView' => 3,
            'spaceBetween' => 0,
        ],
    ],
];
      if ($toolCount > 1) {
        $toolsSwiperConfig['autoplay'] = [
          'delay' => 0,
          'disableOnInteraction' => false,
          'pauseOnMouseEnter' => false,
          'reverseDirection' => true,
        ];
      }
    @endphp
    <section id="tools-technologies" class="tools-technologies section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Tools &amp; Technologies</h2>
      </div>

      @if($toolItems !== [])
        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="swiper init-swiper tools-technologies-slider">
            <script type="application/json" class="swiper-config">
              @json($toolsSwiperConfig)
            </script>
            <div class="swiper-wrapper">
              @foreach($toolItems as $tool)
                @php
                  $toolIcon = trim((string) ($tool['icon'] ?? ''));
                  $toolImage = trim((string) ($tool['image_url'] ?? ''));
                  $toolLogo = filter_var($toolIcon, FILTER_VALIDATE_URL)
                    ? $toolIcon
                    : (filter_var($toolImage, FILTER_VALIDATE_URL) ? $toolImage : '');
                  $toolWebsite = trim((string) ($tool['website_url'] ?? ''));
                  $toolWebsiteScheme = strtolower((string) parse_url($toolWebsite, PHP_URL_SCHEME));
                  $toolWebsite = in_array($toolWebsiteScheme, ['http', 'https'], true) ? $toolWebsite : '';
                @endphp
                <div class="swiper-slide">
                  @if($toolWebsite !== '')
                    <a href="{{ $toolWebsite }}" target="_blank" rel="noopener noreferrer" class="tool-technology-item">
                  @else
                    <div class="tool-technology-item">
                  @endif
                      <div class="tool-technology-logo">
                        @if($toolLogo !== '')
                          <img src="{{ $toolLogo }}" alt="{{ $tool['name'] ?? 'Tool' }}" loading="lazy">
                        @else
                          <i class="bi bi-tools" aria-hidden="true"></i>
                        @endif
                      </div>
                      <span>{{ $tool['name'] ?? '' }}</span>
                  @if($toolWebsite !== '')
                    </a>
                  @else
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    </section><!-- /Tools & Technologies Section -->

    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Portfolio</h2>
        <p>Projek yang saya kerjakan & Kembangkan</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">

          <ul class="portfolio-filters isotope-filters" data-aos="fade-up" data-aos-delay="100">
            <li data-filter="*" class="filter-active">All</li>
            @foreach($categories as $category)
              @if(is_array($category) && !empty($category['name']) && !empty($category['slug']))
                <li data-filter=".filter-{{ $category['slug'] }}">{{ $category['name'] }}</li>
              @endif
            @endforeach
          </ul><!-- End Portfolio Filters -->

          <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="200">

    @php
      $hasInstagramEmbed = false;
    @endphp

    @if(!empty($projects))

        @foreach($projects as $id => $project)

            @php
              $projectCategory = (string) ($project['category'] ?? '');
              $projectCategorySlug = \Illuminate\Support\Str::slug($projectCategory);
              $projectCategoryName = $projectCategory;
              foreach ($categories as $category) {
                if (is_array($category) && ($category['slug'] ?? '') === $projectCategorySlug) {
                  $projectCategoryName = $category['name'] ?? $projectCategory;
                  break;
                }
              }
            @endphp
            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-{{ $projectCategorySlug }}">

                @php
                  $image = $project['image'] ?? 'img/portfolio/app-1.jpg';
                  $imageUrl = filter_var($image, FILTER_VALIDATE_URL) ? $image : asset($image);
                  $projectUrl = $project['project_url'] ?? '';
                  $instagramUrl = trim((string) ($project['instagram_url'] ?? ''));
                  $configuredInstagramAspectRatio = (string) ($project['instagram_aspect_ratio'] ?? '4:5');
                  $instagramAspectRatio = in_array(
                    $configuredInstagramAspectRatio,
                    ['4:5', '1:1', '9:16', '16:9'],
                    true
                  ) ? $configuredInstagramAspectRatio : '4:5';
                  $instagramRatioCss = str_replace(':', ' / ', $instagramAspectRatio);
                  $hasValidInstagramUrl = (bool) preg_match(
                    '/^https:\/\/(www\.)?instagram\.com\/(p|reel)\/[A-Za-z0-9_-]+\/?(?:\?.*)?$/i',
                    $instagramUrl
                  );
                  $hasInstagramEmbed = $hasInstagramEmbed || $hasValidInstagramUrl;
                  $techStack = is_array($project['tech_stack'] ?? null) ? $project['tech_stack'] : [];
                @endphp
                @if($hasValidInstagramUrl)
                  <div
                    class="portfolio-instagram-embed"
                    style="--instagram-ratio: {{ $instagramRatioCss }};"
                  >
                    <blockquote
                      class="instagram-media"
                      data-instgrm-permalink="{{ $instagramUrl }}"
                      data-instgrm-version="14"
                    >
                      <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer">
                        Lihat konten Instagram
                      </a>
                    </blockquote>
                  </div>
                @else
                  <img src="{{ $imageUrl }}"
                       class="img-fluid"
                       alt="{{ $project['title'] ?? 'Portfolio' }}">
                @endif

                <div class="portfolio-info">

                    <h4>{{ $project['title'] ?? 'Untitled Project' }}</h4>

                    <p>
                          {{ $project['description'] ?? (implode(', ', $techStack) ?: ($projectCategoryName ?: ($project['status'] ?? ''))) }}
                    </p>

                    <a href="{{ $hasValidInstagramUrl ? $instagramUrl : $imageUrl }}"
                       title="{{ $project['title'] ?? 'Portfolio' }}"
                       data-gallery="portfolio-gallery-app"
                       class="glightbox preview-link">
                        <i class="bi bi-zoom-in"></i>
                    </a>

                    <a href="{{ $projectUrl !== '' ? $projectUrl : url('/portfolio') }}"
                       title="More Details"
                       class="details-link">
                        <i class="bi bi-link-45deg"></i>
                    </a>

                </div>

            </div>

        @endforeach

    @else

        <div class="col-12">
            <p>Belum ada project portfolio.</p>
        </div>

    @endif

            @if(false)
            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-product">
              <img src="{{ asset('img/portfolio/product-2.jpg') }}" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Product 2</h4>
                <p>Lorem ipsum, dolor sit amet consectetur</p>
                <a href="{{ asset('img/portfolio/product-2.jpg') }}" title="Product 2" data-gallery="portfolio-gallery-product" class="glightbox preview-link"><i class="bi bi-zoom-in"></i></a>
                <a href="portfolio-details.html" title="More Details" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div><!-- End Portfolio Item -->

            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-branding">
              <img src="{{ asset('img/portfolio/branding-2.jpg') }}" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Branding 2</h4>
                <p>Lorem ipsum, dolor sit amet consectetur</p>
                <a href="{{ asset('img/portfolio/branding-2.jpg') }}" title="Branding 2" data-gallery="portfolio-gallery-branding" class="glightbox preview-link"><i class="bi bi-zoom-in"></i></a>
                <a href="portfolio-details.html" title="More Details" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div><!-- End Portfolio Item -->

            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-books">
              <img src="{{ asset('img/portfolio/books-2.jpg') }}" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Books 2</h4>
                <p>Lorem ipsum, dolor sit amet consectetur</p>
                <a href="{{ asset('img/portfolio/books-2.jpg') }}" title="Branding 2" data-gallery="portfolio-gallery-book" class="glightbox preview-link"><i class="bi bi-zoom-in"></i></a>
                <a href="portfolio-details.html" title="More Details" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div><!-- End Portfolio Item -->

            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-app">
              <img src="{{ asset('img/portfolio/app-3.jpg') }}" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>App 3</h4>
                <p>Lorem ipsum, dolor sit amet consectetur</p>
                <a href="{{ asset('img/portfolio/app-3.jpg') }}" title="App 3" data-gallery="portfolio-gallery-app" class="glightbox preview-link"><i class="bi bi-zoom-in"></i></a>
                <a href="portfolio-details.html" title="More Details" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div><!-- End Portfolio Item -->

            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-product">
              <img src="{{ asset('img/portfolio/product-3.jpg') }}" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Product 3</h4>
                <p>Lorem ipsum, dolor sit amet consectetur</p>
                <a href="{{ asset('img/portfolio/product-3.jpg') }}" title="Product 3" data-gallery="portfolio-gallery-product" class="glightbox preview-link"><i class="bi bi-zoom-in"></i></a>
                <a href="portfolio-details.html" title="More Details" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div><!-- End Portfolio Item -->

            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-branding">
              <img src="{{ asset('img/portfolio/branding-3.jpg') }}" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Branding 3</h4>
                <p>Lorem ipsum, dolor sit amet consectetur</p>
                <a href="{{ asset('img/portfolio/branding-3.jpg') }}" title="Branding 2" data-gallery="portfolio-gallery-branding" class="glightbox preview-link"><i class="bi bi-zoom-in"></i></a>
                <a href="portfolio-details.html" title="More Details" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div><!-- End Portfolio Item -->

            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-books">
              <img src="{{ asset('img/portfolio/books-3.jpg') }}" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Books 3</h4>
                <p>Lorem ipsum, dolor sit amet consectetur</p>
                <a href="{{ asset('img/portfolio/books-3.jpg') }}" title="Branding 3" data-gallery="portfolio-gallery-book" class="glightbox preview-link"><i class="bi bi-zoom-in"></i></a>
                <a href="portfolio-details.html" title="More Details" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div><!-- End Portfolio Item -->

            @endif

          </div><!-- End Portfolio Container -->

        </div>

      </div>

    </section><!-- /Portfolio Section -->

    <!-- Pricing Section -->
    <section id="pricing" class="pricing section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        @php
          $pricingSection = is_array($pricing['section'] ?? null) ? $pricing['section'] : [];
          $pricingItems = [];
          if (is_array($pricing['items'] ?? null)) {
            foreach ($pricing['items'] as $pricingItem) {
              if (is_array($pricingItem)) {
                $pricingItems[] = $pricingItem;
              }
            }
          }
          usort($pricingItems, static fn (array $left, array $right): int => ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0)));
        @endphp
        <h2>{{ $pricingSection['title'] ?? 'Pricing' }}</h2>
        <p>{{ $pricingSection['subtitle'] ?? 'Projek yang saya kerjakan & Kembangkan' }}</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4 gx-lg-5">

          @foreach($pricingItems as $pricingItem)
            @php
              $features = is_array($pricingItem['features'] ?? null) ? $pricingItem['features'] : [];
              $buttonLink = trim((string) ($pricingItem['button_link'] ?? ''));
            @endphp
            <div class="col-lg-6">
              <div class="pricing-item d-flex justify-content-between">
                <div>
                  <h3>
                    {{ $pricingItem['name'] ?? '' }}
                    @if(!empty($pricingItem['popular']))
                      <span class="badge bg-primary">Popular</span>
                    @endif
                  </h3>
                  @if(!empty($pricingItem['description']))
                    <p>{{ $pricingItem['description'] }}</p>
                  @endif
                  @if($features !== [])
                    <ul>
                      @foreach($features as $feature)
                        <li>{{ is_array($feature) ? ($feature['feature'] ?? '') : $feature }}</li>
                      @endforeach
                    </ul>
                  @endif
                  @if(!empty($pricingItem['button_text']) && $buttonLink !== '')
                    <a href="{{ $buttonLink }}" class="btn btn-primary">
                      {{ $pricingItem['button_text'] }}
                    </a>
                  @endif
                </div>
                <h4>
                  {{ $pricingItem['price'] ?? '' }}
                  @if(!empty($pricingItem['period']))
                    <small>{{ $pricingItem['period'] }}</small>
                  @endif
                </h4>
              </div>
            </div><!-- End Pricing Item -->
          @endforeach

        </div>

      </div>

    </section><!-- /Pricing Section -->

    <!-- Faq Section -->
    <section id="faq" class="faq section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="content px-xl-5">
              @php
                $faqSection = is_array($faq['section'] ?? null) ? $faq['section'] : [];
                $faqItems = [];
                if (is_array($faq['items'] ?? null)) {
                  foreach ($faq['items'] as $faqItem) {
                    if (is_array($faqItem)) {
                      $faqItems[] = $faqItem;
                    }
                  }
                }
                usort($faqItems, static fn (array $left, array $right): int => ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0)));
              @endphp
              <h3>{{ $faqSection['title'] ?? 'Frequently Asked Questions' }}</h3>
              <p>{{ $faqSection['description'] ?? '' }}</p>
            </div>
          </div>

          <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">

            <div class="faq-container">
              @foreach($faqItems as $faqIndex => $faqItem)
                <div class="faq-item{{ $faqIndex === 0 ? ' faq-active' : '' }}">
                  <h3><span class="num">{{ $faqIndex + 1 }}.</span> <span>{{ $faqItem['question'] ?? '' }}</span></h3>
                  <div class="faq-content">
                    <p>{{ $faqItem['answer'] ?? '' }}</p>
                  </div>
                  <i class="faq-toggle bi bi-chevron-right"></i>
                </div><!-- End Faq item-->
              @endforeach

            </div>

          </div>
        </div>

      </div>

    </section><!-- /Faq Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section accent-background">

      <img src="{{ asset('img/testimonials-bg.jpg') }}" class="testimonials-bg" alt="">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        @php
          $testimonialItems = [];
          if (is_array($testimonials ?? null)) {
            foreach ($testimonials as $testimonial) {
              if (is_array($testimonial) && (($testimonial['active'] ?? true) === true || ($testimonial['active'] ?? true) === 1 || ($testimonial['active'] ?? true) === '1')) {
                $testimonialItems[] = $testimonial;
              }
            }
          }
          usort($testimonialItems, static fn (array $left, array $right): int => ((int) ($left['order'] ?? 0)) <=> ((int) ($right['order'] ?? 0)));
          $testimonialCount = count($testimonialItems);
          $testimonialSwiperConfig = [
            'loop' => $testimonialCount > 1,
            'speed' => 600,
            'slidesPerView' => 'auto',
            'pagination' => [
              'el' => '.swiper-pagination',
              'type' => 'bullets',
              'clickable' => true,
            ],
          ];
          if ($testimonialCount > 1) {
            $testimonialSwiperConfig['autoplay'] = ['delay' => 5000];
          }
        @endphp

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            @json($testimonialSwiperConfig)
          </script>
          <div class="swiper-wrapper">

            @foreach($testimonialItems as $testimonial)
              @php
                $image = trim((string) ($testimonial['image'] ?? ''));
                $imageUrl = filter_var($image, FILTER_VALIDATE_URL)
                  ? $image
                  : asset($image !== '' ? $image : 'img/testimonials/testimonials-1.jpg');
                $rating = min(5, max(0, (int) ($testimonial['rating'] ?? 0)));
              @endphp
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <img src="{{ $imageUrl }}" class="testimonial-img" alt="{{ $testimonial['name'] ?? 'Testimonial' }}">
                  <h3>{{ $testimonial['name'] ?? '' }}</h3>
                  <h4>{{ $testimonial['position'] ?? '' }}</h4>
                  <div class="stars">
                    @for($star = 0; $star < $rating; $star++)
                      <i class="bi bi-star-fill"></i>
                    @endfor
                  </div>
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span>{{ $testimonial['content'] ?? '' }}</span>
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                </div>
              </div><!-- End testimonial item -->
            @endforeach

          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      @php
        $contactData = is_array($contact ?? null) ? $contact : [];
        $contactLink = static function (mixed $value, array $allowedSchemes): ?string {
          $link = trim((string) $value);
          $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));

          return $link !== '' && in_array($scheme, $allowedSchemes, true) ? $link : null;
        };
        $addressLink = $contactLink($contactData['address_link'] ?? '', ['http', 'https']);
        $phoneLink = $contactLink($contactData['phone_link'] ?? '', ['http', 'https']);
        $emailLink = $contactLink($contactData['email_link'] ?? '', ['mailto']);
      @endphp

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Hubungi saya untuk informasi lebih lanjut</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="info-wrap" data-aos="fade-up" data-aos-delay="200">
          <div class="row gy-5">

            <div class="col-lg-4">
              @if($addressLink)
              <a href="{{ $addressLink }}" class="info-item d-flex align-items-center text-reset text-decoration-none">
              @else
              <div class="info-item d-flex align-items-center">
              @endif
                <i class="bi bi-geo-alt flex-shrink-0"></i>
                <div>
                  <h3>Address</h3>
                  <p>{{ $contactData['address'] ?? 'A108 Adam Street, New York, NY 535022' }}</p>
                </div>
              {!! $addressLink ? '</a>' : '</div>' !!}
            </div><!-- End Info Item -->

            <div class="col-lg-4">
              @if($phoneLink)
              <a href="{{ $phoneLink }}" class="info-item d-flex align-items-center text-reset text-decoration-none">
              @else
              <div class="info-item d-flex align-items-center">
              @endif
                <i class="bi bi-telephone flex-shrink-0"></i>
                <div>
                  <h3>Call Us</h3>
                  <p>{{ $contactData['phone'] ?? '+1 5589 55488 55' }}</p>
                </div>
              {!! $phoneLink ? '</a>' : '</div>' !!}
            </div><!-- End Info Item -->

            <div class="col-lg-4">
              @if($emailLink)
              <a href="{{ $emailLink }}" class="info-item d-flex align-items-center text-reset text-decoration-none">
              @else
              <div class="info-item d-flex align-items-center">
              @endif
                <i class="bi bi-envelope flex-shrink-0"></i>
                <div>
                  <h3>Email Us</h3>
                  <p>{{ $contactData['email'] ?? 'info@example.com' }}</p>
                </div>
              {!! $emailLink ? '</a>' : '</div>' !!}
            </div><!-- End Info Item -->

          </div>
        </div>

        <form action="{{ route('contact.send') }}" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="300">
          @csrf
          <div class="row gy-4">

            <div class="col-md-6">
              <input type="text" name="name" class="form-control" placeholder="Your Name" required="">
            </div>

            <div class="col-md-6 ">
              <input type="email" class="form-control" name="email" placeholder="Your Email" required="">
            </div>

            <div class="col-md-12">
              <input type="text" class="form-control" name="subject" placeholder="Subject" required="">
            </div>

            <div class="col-md-12">
              <textarea class="form-control" name="message" rows="6" placeholder="Message" required=""></textarea>
            </div>

            <div class="col-md-12 text-center">
              <div class="loading">Loading</div>
              <div class="error-message"></div>
              <div class="sent-message">Your message has been sent. Thank you!</div>

              <button type="submit">Send Message</button>
            </div>

          </div>
        </form><!-- End Contact Form -->

      </div>

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer accent-background">

    <div class="container">
      <div class="copyright text-center ">
        <p>© <span>Copyright</span> <strong class="px-1 sitename">2026</strong> <span>All Rights Reserved</span></p>
      </div>
      <div class="social-links d-flex justify-content-center">
    @php
        $socialMedia = app(\App\Services\FirebaseService::class)
            ->getDatabase()
            ->getReference('social_media')
            ->getValue();

        $socialMedia = is_array($socialMedia)
            ? $socialMedia
            : [];

        // Hanya tampilkan social media yang aktif
        $socialMedia = array_filter(
            $socialMedia,
            function ($social) {
                return is_array($social)
                    && ($social['active'] ?? false) === true;
            }
        );

        // Urutkan berdasarkan order
        usort(
            $socialMedia,
            function ($a, $b) {
                return ((int) ($a['order'] ?? 0))
                    <=> ((int) ($b['order'] ?? 0));
            }
        );
    @endphp

        @foreach ($socialMedia as $social)

            @php
                $name = trim((string) ($social['name'] ?? ''));
                $iconUrl = trim((string) ($social['icon_url'] ?? ''));
                $url = trim((string) ($social['url'] ?? ''));
            @endphp

        @if ($name !== '' && $iconUrl !== '' && $url !== '')

            <a
                href="{{ $url }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="{{ $name }}"
                title="{{ $name }}"
            >
                <img
                    src="{{ $iconUrl }}"
                    alt="{{ $name }}"
                    loading="lazy"
                    class="social-icon-img"
                >
            </a>

        @endif
        @endforeach
      </div>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> Distributed by <a href="https://themewagon.com" target="_blank">ThemeWagon</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('vendor/typed.js/typed.umd.js') }}"></script>
  <script src="{{ asset('vendor/waypoints/noframework.waypoints.js') }}"></script>
  <script src="{{ asset('vendor/purecounter/purecounter_vanilla.js') }}"></script>
  <script src="{{ asset('vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
  <script src="{{ asset('vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  @if($hasInstagramEmbed ?? false)
    <script async src="https://www.instagram.com/embed.js"></script>
  @endif

  <!-- Main JS File -->
  <script src="{{ asset('js/main.js') }}"></script>

</body>

</html>