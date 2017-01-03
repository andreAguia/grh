<?php
/**
 * Estatística
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase');
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    ################################################################
    
    switch ($fase)
    {
        case "":
            # Limita o tamanho da tela
            $grid1 = new Grid();
            $grid1->abreColuna(12);
            
            botaoVoltar("grh.php");            
            titulo("Estatíitica");
            
            $grid2 = new Grid();
            $grid2->abreColuna(6);
            titulo("Estatíitica");
            
            $grid2->fechaColuna();
            $grid2->abreColuna(6);
            titulo("Estatíitica");
            
            echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
                  <script type='text/javascript'>
                  google.charts.load('current', {packages:['corechart']});
                  google.charts.setOnLoadCallback(drawChart);
              function drawChart() {
                var data = google.visualization.arrayToDataTable([
                  ['Task', 'Hours per Day'],
                  ['Work',     11],
                  ['Eat',      2],
                  ['Commute',  2],
                  ['Watch TV', 2],
                  ['Sleep',    7]
                ]);

                var options = {
                  title: 'My Daily Activities',
                  is3D: true,
                };

                var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
                chart.draw(data, options);
              }
            </script>";
          
            echo '<div id="piechart_3d" style="width: 600px; height: 600px;"></div>';
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            
            $grid1->fechaColuna();
            $grid1->fechaGrid();
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}