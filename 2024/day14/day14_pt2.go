package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

type Robot struct {
	x         int
	y         int
	velocityX int
	velocityY int
}

var maxX, maxY int

func main() {
	filenames := []string{"input"}
	for _, fileName := range filenames {
		if fileName == "testInput" {
			maxX = 10
			maxY = 6
		} else {
			maxX = 101 - 1
			maxY = 103 - 1
		}

		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		//sleepDuration, _ := time.ParseDuration("0.0s")
		for second := 0; ; second++ {
			//time.Sleep(sleepDuration)
			robotMap := make([][]int, maxY+1)
			for y := 0; y <= maxY; y++ {
				robotMap[y] = make([]int, maxX+1)
			}
			println(second)
			for _, line := range lines {
				robot := Robot{}
				lineParts := strings.Fields(line)
				firstCommaPosition := strings.Index(lineParts[0], ",")
				robot.x, err = strconv.Atoi(lineParts[0][2:firstCommaPosition])
				if err != nil {
					panic(err)
				}
				robot.y, err = strconv.Atoi(lineParts[0][firstCommaPosition+1:])
				if err != nil {
					panic(err)
				}
				secondCommaPosition := strings.Index(lineParts[1], ",")
				robot.velocityX, err = strconv.Atoi(lineParts[1][2:secondCommaPosition])
				if err != nil {
					panic(err)
				}
				robot.velocityY, err = strconv.Atoi(lineParts[1][secondCommaPosition+1:])
				if err != nil {
					panic(err)
				}

				newX, newY := predictPosition(robot, second)
				robotMap[newY][newX]++
			}
			allOnes := true
			for y := 0; y <= maxY; y++ {
				for x := 0; x <= maxX; x++ {
					if robotMap[y][x] == 0 {
					} else {
						if robotMap[y][x] > 1 {
							allOnes = false
						}
					}
				}
			}
			if allOnes {
				for y := 0; y <= maxY; y++ {
					print("\n")
					for x := 0; x <= maxX; x++ {
						if robotMap[y][x] == 0 {
							print(".")
						} else {
							if robotMap[y][x] > 1 {
								allOnes = false
							}
							print(robotMap[y][x])
						}
					}
				}
				print("\n")

				os.Exit(0)
			}
		}

	}

}
func predictPosition(robot Robot, seconds int) (int, int) {
	newX := robot.x + seconds*robot.velocityX
	newY := robot.y + seconds*robot.velocityY

	newX = newX % (maxX + 1)
	newY = newY % (maxY + 1)
	if newX < 0 {
		newX = maxX + newX + 1
	}
	if newY < 0 {
		newY = maxY + newY + 1
	}

	return newX, newY
}
