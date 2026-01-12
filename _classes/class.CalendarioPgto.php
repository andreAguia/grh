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
            ["Abril", "06/05"],
            ["Maio", "03/06"],
            ["13º Salário (1ª Parcela)", "30/06"],
            ["Junho", "03/07"],
            ["Julho", "05/08"],
            ["Agosto", "03/09"],
            ["Setembro", "05/10"],
            ["Outubro", "05/11"],
            ["Novembro", "03/12"],
            ["13º Salário (2ª Parcela)", "18/12"],
            ["Dezembro", "06/01/2026"]
        ];

        # Exemplo mais complexo
        $tabela = new Tabela();
        $tabela->set_titulo("Calendário de Pagamento");
        $tabela->set_subtitulo("2025");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Mês de Competência", "Data do Pagamento"]);
        $tabela->set_align(["left", "center"]);
        
        
        
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                  'valor' => get_nomeMes(date('m')),
                  'operador' => '=',
                  'id' => 'calendarioPgto')));        
        
        $tabela->show();
    }

    ###########################################################
}
