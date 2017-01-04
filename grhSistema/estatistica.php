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
    $page->set_jscript('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);
    
    # Cria um menu
        $menu1 = new MenuBar();

    # Voltar
    $linkVoltar = new Link("Voltar","grh.php");
    $linkVoltar->set_class('button');
    $linkVoltar->set_title('Voltar para página anterior');
    $linkVoltar->set_accessKey('V');
    $menu1->add_link($linkVoltar,"left");

    # Por Perfil
    $linkRel = new Link("por Perfil","?");
    $linkRel->set_class('button');
    $linkRel->set_title('Relatórios dos Sistema');
    #$linkRel->set_accessKey('R');
    $menu1->add_link($linkRel,"right");
    
    # Por Lotação
    $linkRel = new Link("por Lotação","?fase=lotacao");
    $linkRel->set_class('button');
    $linkRel->set_title('Relatórios dos Sistema');
    #$linkRel->set_accessKey('R');
    $menu1->add_link($linkRel,"right");

    $menu1->show();
    
    titulo("Estatística");
    br();
    
    ################################################################
    
    switch ($fase)
    {
        case "":
            $grid2 = new Grid();
            $grid2->abreColuna(6);
            
            # Pega os dados
            $selectGrafico = 'SELECT tbperfil.nome, count(tbservidor.matricula) 
                                FROM tbservidor LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                               WHERE tbservidor.situacao = 1
                            GROUP BY tbperfil.nome';

            $servidores = $pessoal->select($selectGrafico);
            $numServidores = $pessoal->count($selectGrafico);
            
            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores por Perfil");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Perfil","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(6);
            
            echo "<script type='text/javascript'>
                  google.charts.load('current', {packages:['corechart']});
                  google.charts.setOnLoadCallback(drawChart);
              function drawChart() {
                var data = google.visualization.arrayToDataTable([";
            $contador = 0;
            echo "['Perfil', 'Número de Servidores'],";
            foreach ($servidores as $item){
                echo "['".$item[0]."',".$item[1]."]";
                if($contador < $numServidores-1){
                    echo ",";
                }
                $contador++;
            }
            
            echo "]);";
            
            
            #      ['Task', 'Hours per Day'],
            #      ['Work',     11],
            #      ['Eat',      2],
            #      ['Commute',  2],
            #      ['Watch TV', 2],
            #      ['Sleep',    7]
            #    ]);

            echo "var options = {
                  title: '',
                  is3D: true,
                };

                var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
                chart.draw(data, options);
              }
            </script>";
          
            echo '<div id="piechart_3d" style="width: 600px; height: 600px;"></div>';
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            hr();
            break;
            
            #########################################
            
            case "lotacao":
            $grid2 = new Grid();
            $grid2->abreColuna(6);
            
            # Pega os dados
            $selectGrafico = 'SELECT tblotacao.dir, count(tbservidor.matricula) 
                                FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                 AND situacao = 1
                                 AND ativo
                            GROUP BY tblotacao.dir';

            $servidores = $pessoal->select($selectGrafico);
            $numServidores = $pessoal->count($selectGrafico);
            
            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores por Lotação (Diretoria)");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Lotação","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->abreColuna(6);
            
            echo "<script type='text/javascript'>
                  google.charts.load('current', {packages:['corechart']});
                  google.charts.setOnLoadCallback(drawChart);
              function drawChart() {
                var data = google.visualization.arrayToDataTable([";
            $contador = 0;
            echo "['Perfil', 'Número de Servidores'],";
            foreach ($servidores as $item){
                echo "['".$item[0]."',".$item[1]."]";
                if($contador < $numServidores-1){
                    echo ",";
                }
                $contador++;
            }
            
            echo "]);";
            
            
            #      ['Task', 'Hours per Day'],
            #      ['Work',     11],
            #      ['Eat',      2],
            #      ['Commute',  2],
            #      ['Watch TV', 2],
            #      ['Sleep',    7]
            #    ]);

            echo "var options = {
                  title: '',
                  is3D: true,
                };

                var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
                chart.draw(data, options);
              }
            </script>";
          
            echo '<div id="piechart_3d" style="width: 600px; height: 600px;"></div>';
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            
            hr();
            break;
    }
    
    # Fecha o grid
    $grid1->fechaColuna();
    $grid1->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}