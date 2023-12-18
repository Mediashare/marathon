# Marathon
## Introduction
Marathon is a command-line tool written in PHP and Symfony that empowers you to efficiently **manage tasks for your projects**. 
It provides a comprehensive solution for maintaining a project-related activities through commit history.
### Features
- **Commit:** Easily associate time entries with project commits to maintain a detailed history of actions taken during the development process.
- **Efficient Time Management:** Streamline your workflow by seamlessly integrating time into your version control system. Focus on development while keeping an accurate record of time spent on each task.
- **Symfony Framework:** Built on the robust Symfony framework, ensuring reliability, scalability, and ease of maintenance for your time management needs.
## Installation
### Composer
#### Basic
```bash
composer require mediashre/marathon
bin/marathon <COMMAND>
```
#### Global
```bash
composer global require mediashre/marathon
marathon <COMMAND>
```
### Binary
```bash
curl --output marathon https://raw.githubusercontent.com/Mediashare/marathon/main/marathon
chmod 755 marathon
sudo cp marathon /usr/local/bin/marathon
marathon <COMMAND>
```
## Usage
Here are some examples of how to use Marathon:
- To check the time you spend on a project, you can create a task for each phase of the project.
- To check the time you spend on a recurring task, you can create a task with a start date and an end date.
- To check the time you spend on a task with a client or vendor, you can add this information to the task.

```bash
  marathon task:list                        Displaying the tasks list
  marathon task:start                       Starting step of task selected
  marathon task:stop                        Stoping step of task selected
  marathon task:status                      Displaying status of task selected
  marathon task:archive                     Archiving the task selected
  marathon task:delete                      Deleting the task selected

  marathon commit <?COMMIT_MESSAGE>         Creating new commit into task selected
  marathon commit:edit <?COMMIT_ID>         Editing the commit from task
  marathon commit:delete <?COMMIT_ID>       Deleting the commit from task selected
  
  marathon marathon:gitignore               Adding .marathon rule into .gitgnore
  marathon marathon:upgrade                 Upgrading to latest version of Marathon
```
## Contributing
Marathon is an open-source project. You can contribute to the project by submitting bug fixes, improvements, or new features.

To contribute to the project, you can follow these instructions:
- Clone the marathon GitHub repository
- Create a branch for your contribution
- Make your changes
- Test your changes with `bin/phpunit`
- Build your bin with `box compile`
- Submit a pull request

### Build a bin with Box
#### Box install
[Box2](https://github.com/box-project/box) used for binary generation from php project. **PHP >=8.1 is required.**
```bash
composer global require humbug/box
box
```
#### Box usage
```bash
composer dump-env dev
box compile
```
## Conclusion
Marathon is a simple and effective tool that can help you better manage your time. If you are looking for a free and open-source time tracker, Marathon is a good option.