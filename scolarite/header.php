
<style>
    .nav-header {
        display: flex;
        align-items: center;
       
    }
    .brand-logo {
        display: flex;
        align-items: center;
    }
    .brand-logo img {
        max-height: 100px;
        max-width: 100px;/* Ajustez la taille du logo selon vos besoins */
    }
    .titre {
        font-size: 30px; /* Ajustez la taille du texte selon vos besoins */
        margin-right: 150px; /* Espace entre le texte et le logo */
    }
     .titre1 {
        font-size: 30px; /* Ajustez la taille du texte selon vos besoins */
      }
       @media (min-width: 992px) {
        .titre {
            margin-left: 0; /* Augmentez la marge sur les écrans larges si nécessaire */
        }
        .titre1 {
        font-size: 25px; /* Ajustez la taille du texte selon vos besoins */
      
    }
      
    }
      @media (min-width: 250px) {
    
        .titre1 {
        font-size: 10px; /* Ajustez la taille du texte selon vos besoins */
        margin-right: 150px; /* Espace entre le texte et le logo */
    }
    }
</style>

<div class="nav-header">
<a href="#" class="brand-logo">
  
        <img class="logo-abbr" src="../administrateur/<?php echo  $_SESSION['logo_univ']?>" alt="">
       
    </a>
     <span class="titre d-none d-md-inline text-white"><?php echo $_SESSION['etablissement']; ?></span>


            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
      
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                            <div class="search_bar dropdown">
                                <span class="search_icon p-3 c-pointer" data-toggle="dropdown">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <div class="dropdown-menu p-0 m-0">
                                    <form>
                                        <input class="form-control" type="search" placeholder="Recherchez" aria-label="Search">
                                    </form>
                                </div>
                            </div>
                        </div>

                        <ul class="navbar-nav header-right">
                          
                            <li class="nav-item dropdown header-profile">
                            <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <img src="<?php echo $_SESSION['img']?>" width="20" alt="">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="compte" class="dropdown-item ai-icon">
                                        <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <span class="ml-2">Mon profil </span>
                                    </a>
                                   
                                    <a href="deconnexion" class="dropdown-item ai-icon">
                                        <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                        <span class="ml-2">Deconnexion </span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>