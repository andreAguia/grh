<?php

class Organograma {

    /**
     * Monta um Organograma
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $topo string null A lotação que será o topo do organograma (diretoria ou a reitoria)
     */
    private $topo = null;

###########################################################

    public function __construct($topo = null) {

        /**
         * Inicia a classe
         * 
         * @param $topo  string null A lotação que será o topo do organograma (diretoria ou a reitoria)
         * 
         * @syntax $button = new Chart($tipo,$dados);
         */
        $this->topo = $topo;
    }

###########################################################

    public function show() {
        /**
         * Exibe o gráfico
         * 
         * @syntax $button->show();
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $lotacaoClasse = new Lotacao();

        # Pega as diretorias
        $lotacao = $pessoal->select("SELECT idLotacao, DIR, GER, nome
                                       FROM tblotacao
                                      WHERE ativo
                                        AND DIR = '{$this->topo}'
                                        AND GER <> 'Afastados'
                                        AND GER <> 'Cedidos'
                                        AND GER <> 'CCM'
                                        AND GER <> 'CGAB'
                                        AND GER <> 'Rio'
                                        AND GER <> 'SECR'
                                   ORDER BY DIR, GER");

        # Carrega as rotinas do Google
        echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>";

        # Cria a função
        echo "<script type='text/javascript'>
                google.charts.load('current', {packages:['orgchart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                  var data = new google.visualization.DataTable();
                  data.addColumn('string', 'Name');
                  data.addColumn('string', 'Manager');
                  data.addColumn('string', 'ToolTip');

                  // For each orgchart box, provide the name, manager, and tooltip to show.
                  data.addRows([";
        
        
        
        echo "[{'v':'{$this->topo}','f':'{$this->topo}<div style=\"color:red; font-style:italic; font-size:10px;\">{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}</div>'},'','{$lotacaoClasse->get_nomeDiretoriaSigla($this->topo)}'],";

        # Percorre as lotações
        foreach ($lotacao as $item) {
            #$resp = $pessoal->get_gerente($item['idLotacao']);
            
            echo "['{$item['GER']}<div style=\"color:red; font-style:italic; font-size:10px;\">{$item['nome']}</div> ','{$item['DIR']}','{$item['nome']}'],";
            #echo "['{$item['GER']}','{$item['DIR']}','{$item['nome']}'],";
            #echo "[{'v':'Mike', 'f':'Mike<div style="color:red; font-style:italic">President</div>'}, '', 'The President'],";
        }


        echo "]);";

        echo "        // Create the chart.
                      var chart = new google.visualization.OrgChart(document.getElementById('organograma1'));
                      // Draw the chart, setting the allowHtml option to true for the tooltips.
                      chart.draw(data, {'allowHtml':true});
                    }
             </script>";

        # Abre a div do organograma
        echo "<div id='organograma1'></div>";

        ######################################
        # Para quando for da Reitoria
        if ($this->topo == "Reitoria") {
            # Pega as diretorias
            $lotacao = $pessoal->select("SELECT idLotacao, DIR 
                                       FROM tblotacao
                                      WHERE ativo
                                        AND DIR <> '{$this->topo}'
                                        AND GER <> 'Afastados'
                                        AND GER <> 'Cedidos'                                        
                                   ORDER BY DIR, GER");

            # Carrega as rotinas do Google
            echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>";

            # Cria a função
            echo "<script type='text/javascript'>
                google.charts.load('current', {packages:['orgchart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                  var data = new google.visualization.DataTable();
                  data.addColumn('string', 'Name');
                  data.addColumn('string', 'Manager');
                  data.addColumn('string', 'ToolTip');

                  // For each orgchart box, provide the name, manager, and tooltip to show.
                  data.addRows([";

            # Percorre as lotações
            foreach ($lotacao as $item) {
                #$resp = $pessoal->get_gerente($item['idLotacao']);
                $nomeDir = $lotacaoClasse->get_nomeDiretoria($item['idLotacao']);
                echo "['{$item['DIR']}<div style=\"color:red; font-style:italic; font-size:10px;\">{$nomeDir}</div>','{$this->topo}','{$nomeDir}'],";
                #echo "['{$item['GER']}','{$item['DIR']}','{$item['nome']}'],";
                #echo "[{'v':'Mike', 'f':'Mike<div style="color:red; font-style:italic">President</div>'}, '', 'The President'],";
            }


            echo "]);";

            echo "        // Create the chart.
                      var chart = new google.visualization.OrgChart(document.getElementById('organograma2'));
                      // Draw the chart, setting the allowHtml option to true for the tooltips.
                      chart.draw(data, {'allowHtml':true});
                    }
             </script>";

            # Abre a div do organograma
            echo "<div id='organograma2'></div>";
        }
    }

}
