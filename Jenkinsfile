#!groovy

@Library('platform-jenkins-pipeline') _

pipeline {
    agent { label 'magento2' }

    stages {
        stage('Build Module') {
            steps {
                buildModule('magento2-module')
            }
        }
    }

    post {
        always {
            sendNotifications()
        }
    }
}
