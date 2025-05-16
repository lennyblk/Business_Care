package com.businesscare;

public class Main {
    public static void main(String[] args) {
        try {
            CompleteReportGenerator generator = new CompleteReportGenerator();
            generator.generateCompleteReport();
        } catch (Exception e) {
            System.err.println("Erreur : " + e.getMessage());
            e.printStackTrace();
        }
    }
}
