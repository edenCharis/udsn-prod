<div class="dlabnav">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            <li class="nav-label first">Menu Principal</li>

            <!-- Tableau de bord -->
            
            <?php 
            
            
              if( isset($_SESSION["code_enseignant"] ) and !is_null($_SESSION["code_enseignant"]) ){
            
            ?>
            <li>
                <a href="index" aria-expanded="false">
                    <i class="la la-home"></i>
                    <span class="nav-text">Tableau de bord</span>
                </a>
            </li>
            <li>
                <a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
                    <i class="la la-plus-square-o"></i>
                    <span class="nav-text">Gestion des notes</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="filtre1">Enregistrer note examen</a></li>
                    <li><a href="filtre2">Enregistrer note devoir</a></li>
                </ul>
            </li>
            
           

            <!-- Mon compte -->
              <li>
                <a class="ai-icon" href="enseignement" aria-expanded="false">
                   <i class="fa fa-book-open"></i>

                    <span class="nav-text">Mes Enseignements</span>
                </a>
            </li>
             <li>
                <a class="ai-icon" href="etudiants" aria-expanded="false">
               <i class="fa fa-user-graduate"></i>


                    <span class="nav-text">Mes Etudiants</span>
                </a>
            </li>
                          <li>
                <a class="ai-icon" href="contrat" aria-expanded="false">
              <i class="fa fa-file"></i>



                    <span class="nav-text">Mon Contrat</span>
                </a>
            </li>
            
             <?php } ?>
            <li>
                <a class="ai-icon" href="compte" aria-expanded="false">
                    <i class="la la-user"></i>
                    <span class="nav-text">Mon compte</span>
                </a>
            </li>
        </ul>
    </div>
</div>
