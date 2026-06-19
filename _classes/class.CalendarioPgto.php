<?php

class CalendarioPgto {

    /**
     * Exibe o calendário de pgto
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */

    ###########################################################

    /**
     * Método exibeCalendario
     * Exibe o calendário do ano informado
     * 
     * @param    integer $ano O ano do calendário. se nullo exibe o do ano vigente
     */
    public function exibeCalendario($ano = null) {

        $array = [
            ["Janeiro", "04/02"],
            ["Fevereiro", "04/03"],
            ["Março", "06/04"],
            ["Abril", "01/05"],
            ["Maio", "01/06"],
            ["13º Salário (1ª Parcela)", "26/05"],
            ["Junho", "01/07"],
            ["Julho", "01/08"],
            ["Agosto", "01/09"],
            ["Setembro", "01/10"],
            ["Outubro", "01/11"],
            ["Novembro", "01/12"],
            ["13º Salário (2ª Parcela)", "19/12"],
            ["Dezembro", "01/01/2027"]
        ];

        # Exemplo mais complexo
        $tabela = new Tabela();
        $tabela->set_titulo("Calendário de Pagamento 2026");
        #$tabela->set_subtitulo("2026");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Mês de Competência", "Data do Pagamento"]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        
        
        
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                  'valor' => get_nomeMes(date('m')),
                  'operador' => '=',
                  'id' => 'calendarioPgto')));        
        
        $tabela->show();
    }

    ###########################################################
}
