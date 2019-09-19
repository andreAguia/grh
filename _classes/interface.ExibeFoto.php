<?php

class ExibeFoto{
    /**
     * Gera o Menu Principal do Sistema
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    
    private $fotoLargura = 70;
    private $fotoAltura = 90;
    private $idPessoa = NULL;
    
######################################################################################################################    
    
    public function __construct($fotoLargura = 35, $fotoAltura = 45){
    /**
     * Inicia a classe
     */
        
        # Preenche variável
        $this->fotoLargura = $fotoLargura;
        $this->fotoAltura = $fotoAltura;
    }

######################################################################################################################
    
    /**
     * Método show
     * 
     * Exibe a Foto do servidor
     */
    
    public function show($idPessoa){
        
        # Monta o Menu
        $menu = new MenuGrafico(1);
            
        # Define a pasta
        $arquivo = "../../_fotos/$idPessoa.jpg";

        # Verifica se tem pasta desse servidor
        if(file_exists($arquivo)){
            $botao = new BotaoGrafico("foto");
            $botao->set_url('?fase=exibeFoto&idPessoa='.$idPessoa);
            $botao->set_imagem($arquivo,$this->fotoLargura,$this->fotoAltura);
            $botao->set_title('Foto do Servidor');
            $menu->add_item($botao);
        }else{
            $botao = new BotaoGrafico("foto");
            $botao->set_url('?fase=exibeFoto&idPessoa='.$idPessoa);
            $botao->set_imagem(PASTA_FIGURAS.'foto.png',$this->fotoLargura,$this->fotoAltura);
            $botao->set_title('Servidor sem foto cadastrada');
            $menu->add_item($botao);
        }

        $menu->show();
    }
   
######################################################################################################################
   
}