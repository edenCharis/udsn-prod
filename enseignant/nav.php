

<div class="dlabnav">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            <li class="nav-label first">Menu Principal</li>

            <!-- Tableau de bord -->
            
            <?php 
            
            
              if( isset($_SESSION["code_enseignant"] ) and !is_null($_SESSION["code_enseignant"])  ){
            
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
                    <li><a href="notation1">Enregistrer note examen</a></li>
                    <li><a href="notation2">Enregistrer note devoir</a></li>
                </ul>
            </li>
            
            <?php } ?>

            <!-- Mon compte -->
            <li>
                <a class="ai-icon" href="compte" aria-expanded="false">
                    <i class="la la-user"></i>
                    <span class="nav-text">Mon compte</span>
                </a>
            </li>
        </ul>
    </div>
</div>
