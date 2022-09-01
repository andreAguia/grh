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

        # Pega as diretorias
        if ($this->topo == "Reitoria") {
            $lotacao = $pessoal->select("SELECT idLotacao, DIR, GER, nome
                                       FROM tblotacao
                                      WHERE ativo
                                        AND GER <> 'Afastados'
                                        AND GER <> 'Cedidos'
                                   ORDER BY DIR, GER");
        } else {
            $lotacao = $pessoal->select("SELECT idLotacao, DIR, GER, nome
                                       FROM tblotacao
                                      WHERE ativo
                                        AND DIR = '{$this->topo}'
                                   ORDER BY DIR, GER");
        }

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

        if ($this->topo == "Reitoria") {
            $diretorias = $pessoal->select("SELECT DISTINCT DIR
                                              FROM tblotacao
                                             WHERE ativo
                                               AND DIR <> 'Reitoria'
                                               AND GER <> 'SECR'
                                          ORDER BY DIR");
            
            foreach($diretorias as $dd){
                echo "['{$dd['DIR']}','{$this->topo}','Nome'],";
            }
        }

        # Percorre as lotações
        foreach ($lotacao as $item) {
            #$resp = $pessoal->get_nome($pessoal->get_gerente($item['idLotacao']));
            echo "['{$item['GER']}<div style=\"color:red; font-style:italic; font-size:11px;\">{$item['nome']}</div>','{$item['DIR']}','{$item['nome']}'],";
            #echo "['{$item['GER']}','{$item['DIR']}','{$item['nome']}'],";
            #echo "[{'v':'Mike', 'f':'Mike<div style="color:red; font-style:italic">President</div>'}, '', 'The President'],";
        }

        echo "]);";

        echo "        // Create the chart.
                      var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
                      // Draw the chart, setting the allowHtml option to true for the tooltips.
                      chart.draw(data, {'allowHtml':true});
                    }
             </script>";

        # Abre a div do organograma
        echo "<div id='chart_div'></div>";
        
        
    }

}
