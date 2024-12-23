package main

import (
	"fmt"
	"os"
	"slices"
	"sort"
	"strings"
)

type Connection struct {
	pc1 string
	pc2 string
}
type Computer struct {
	name               string
	connectedComputers []string
}

var computers map[string]Computer

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		total := 0
		lines := strings.Split(string(fileContents), "\n")
		connections := make([]Connection, len(lines))
		for index, line := range lines {
			connections[index] = Connection{line[0:2], line[3:]}
		}
		computers = make(map[string]Computer)
		for _, connection := range connections {
			pc1, ok := computers[connection.pc1]
			if ok {
				if !slices.Contains(computers[connection.pc1].connectedComputers, connection.pc2) {
					pc1.connectedComputers = append(pc1.connectedComputers, connection.pc2)
					computers[connection.pc1] = pc1
				}
			} else {
				computers[connection.pc1] = Computer{name: connection.pc1, connectedComputers: []string{connection.pc2}}
			}
			pc2, ok := computers[connection.pc2]
			if ok {
				if !slices.Contains(computers[connection.pc2].connectedComputers, connection.pc1) {
					pc2.connectedComputers = append(pc2.connectedComputers, connection.pc1)
					computers[connection.pc2] = pc2
				}
			} else {
				computers[connection.pc2] = Computer{name: connection.pc2, connectedComputers: []string{connection.pc1}}
			}
		}
		threeSets := make([][]string, 0)
		for _, computer := range computers {
			for _, connectedComputerName := range computer.connectedComputers {
				for _, connectedToConnectedName := range computers[connectedComputerName].connectedComputers {
					if slices.Contains(computer.connectedComputers, connectedToConnectedName) {
						alreadyExists := false
						for _, set := range threeSets {
							if set[0] == computer.name && set[1] == connectedComputerName && set[2] == connectedToConnectedName {
								alreadyExists = true
								break
							}
							if set[0] == computer.name && set[1] == connectedToConnectedName && set[2] == connectedComputerName {
								alreadyExists = true
								break
							}
							if set[0] == connectedComputerName && set[1] == computer.name && set[2] == connectedToConnectedName {
								alreadyExists = true
								break
							}
							if set[0] == connectedComputerName && set[1] == connectedToConnectedName && set[2] == computer.name {
								alreadyExists = true
								break
							}
							if set[0] == connectedToConnectedName && set[1] == connectedComputerName && set[2] == computer.name {
								alreadyExists = true
								break
							}
							if set[0] == connectedToConnectedName && set[1] == computer.name && set[2] == connectedComputerName {
								alreadyExists = true
								break
							}
						}
						if !alreadyExists {
							newSet := []string{computer.name, connectedComputerName, connectedToConnectedName}
							sort.Strings(newSet)
							threeSets = append(threeSets, newSet)
						}
					}
				}
			}
		}
		for _, set := range threeSets {
			for _, name := range set {
				if string(name[0]) == "t" {
					//println(strings.Join(set, ","))
					total++
					break
				}
			}
		}

		println(total)
	}
}
